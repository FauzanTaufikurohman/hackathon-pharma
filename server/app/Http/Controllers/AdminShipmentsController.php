<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use App\Models\ShipmentIssue;
use App\Models\ShipmentReport;
use App\Models\User;
use App\OperationsWorkflow;
use App\VendorShipmentService;
use Carbon\CarbonImmutable;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminShipmentsController extends Controller
{
    public function metrics(Request $request, OperationsWorkflow $workflow): JsonResponse
    {
        $query = $workflow->visible($request->user(), 'admin');
        $counts = $query->selectRaw('status, COUNT(*) AS total')->groupBy('status')->pluck('total', 'status');

        return response()->json(['data' => ['total' => (int) $counts->sum(), 'statuses' => $counts,
            'open_issues' => ShipmentIssue::where('status', 'open')->count(),
            'available_reports' => ShipmentReport::count(), 'updated_at' => now()->toISOString()]])->header('Cache-Control', 'private, no-store');
    }

    public function export(Request $request, OperationsWorkflow $workflow): StreamedResponse
    {
        $query = $workflow->visible($request->user(), 'admin');
        abort_if((clone $query)->count() > 10000, 413, 'Ekspor maksimal 10.000 kiriman; gunakan ekspor terjadwal untuk data lebih besar.');

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Resi', 'Pengirim', 'Penerima', 'Tujuan', 'Muatan', 'Koli', 'Status'], ',', '"', '');
            foreach ($query->orderBy('id')->cursor() as $s) {
                $row = [$s->tracking_number, $s->sender_name, $s->recipient_name, $s->destination_city, $s->item_summary, $s->package_count, $s->status];
                $row = array_map(fn ($value) => preg_match('/^[\\s]*[=+@\\-]/u', (string) $value) ? "'".$value : $value, $row);
                fputcsv($out, $row, ',', '"', '');
            }
            fclose($out);
        }, 'manifest-pharma.csv', ['Content-Type' => 'text/csv; charset=UTF-8', 'Cache-Control' => 'private, no-store']);
    }

    public function index(Request $request, OperationsWorkflow $workflow, VendorShipmentService $serializer): JsonResponse
    {
        $query = $workflow->visible($request->user(), 'admin');
        $data = $request->validate(['status' => ['sometimes', Rule::in(array_keys(VendorShipmentService::LABELS))],
            'search' => ['sometimes', 'string', 'max:120'], 'page' => ['sometimes', 'integer', 'between:1,100000'],
            'per_page' => ['sometimes', 'integer', 'between:1,100']]);
        $counts = (clone $query)->selectRaw('status, COUNT(*) AS total')->groupBy('status')->pluck('total', 'status');
        if (isset($data['status'])) {
            $query->where('status', $data['status']);
        }
        if (trim($data['search'] ?? '') !== '') {
            $pattern = '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], mb_strtolower(trim($data['search']))).'%';
            $query->where(fn ($q) => $q->whereRaw("LOWER(tracking_number) LIKE ? ESCAPE '!'", [$pattern])
                ->orWhereRaw("LOWER(recipient_name) LIKE ? ESCAPE '!'", [$pattern]));
        }
        $page = $query->with(['latestTelemetry', 'latestInspection.checker'])->latest('id')->paginate($data['per_page'] ?? 20);

        return response()->json(['data' => $page->getCollection()->map(fn ($s) => $serializer->serialize($s)),
            'counts' => $counts, 'meta' => ['total' => $page->total(), 'current_page' => $page->currentPage(), 'last_page' => $page->lastPage()]])->header('Cache-Control', 'private, no-store');
    }

    public function lookups(Request $request, OperationsWorkflow $workflow): JsonResponse
    {
        $workflow->authorize($request->user(), 'admin');
        $couriers = User::where('role', 'courier')->where('is_active', true)->orderBy('id')->get()
            ->map(function ($user) {
                $reserved = Shipment::where('courier_id', $user->id)->whereNotIn('status', ['completed', 'delivered', 'returned', 'cancelled'])->sum('weight_kg');

                return ['id' => $user->id, 'name' => $user->name, 'capacity_kg' => $user->courier_profile['capacity_kg'] ?? null,
                    'reserved_kg' => (float) $reserved, 'profile' => $user->courier_profile];
            });

        return response()->json(['data' => ['couriers' => $couriers,
            'checkers' => User::where('role', 'checker')->where('is_active', true)->get(['id', 'name']),
            'buyers' => User::whereIn('role', ['buyer', 'vendor'])->where('is_active', true)->get(['id', 'name', 'facility'])]]);
    }

    public function store(Request $request, OperationsWorkflow $workflow): JsonResponse
    {
        $workflow->authorize($request->user(), 'admin');
        $request->merge(['tracking_number' => strtoupper(trim((string) $request->input('tracking_number')))]);
        $rules = ['request_id' => ['required', 'uuid'], 'tracking_number' => ['required', 'string', 'max:100', 'regex:/^[A-Z0-9#][A-Z0-9#_-]{2,99}$/'],
            'buyer_id' => ['required', 'integer', Rule::exists('users', 'id')->where(fn (QueryBuilder $q) => $q->whereIn('role', ['buyer', 'vendor'])->where('is_active', true))],
            'inspector_id' => ['required', 'integer', Rule::exists('users', 'id')->where('role', 'checker')->where('is_active', true)],
            'package_count' => ['required', 'integer', 'between:1,10000'], 'weight_kg' => ['required', 'numeric', 'gt:0', 'max:100000'],
            'temperature_profile' => ['required', Rule::in(['ambient', 'controlled', 'deep_freeze'])],
            'temperature_min' => ['required', 'numeric', 'between:-100,100'],
            'temperature_max' => ['required', 'numeric', 'between:-100,100', 'gt:temperature_min'],
            'scheduled_at' => ['required', 'date'], 'recipient_phone' => ['required', 'string', 'max:50', 'regex:/^\\+?[0-9 ()-]{8,25}$/'],
            'address' => ['required', 'string', 'max:2000'], 'handling_instructions' => ['required', 'string', 'max:4000'],
            'batch_number' => ['sometimes', 'nullable', 'string', 'max:255'], 'package_type' => ['sometimes', 'nullable', 'string', 'max:255']];
        foreach (['sender_name', 'recipient_name', 'origin_city', 'destination_city', 'item_summary'] as $field) {
            $rules[$field] = ['required', 'string', 'min:2', 'max:255'];
        }
        $data = $request->validate($rules);

        return response()->json($workflow->perform($request->user(), 'admin', null, 'create', $data, function () use ($data, $request, $workflow) {
            $fields = collect($data)->except('request_id')->all();
            $fields['scheduled_at'] = CarbonImmutable::parse($fields['scheduled_at'])->utc();
            $shipment = Shipment::create($fields + ['status' => 'inspection', 'created_by' => $request->user()->id,
                'last_location' => $fields['origin_city'], 'is_demo' => false, 'version' => 0]);

            return $workflow->result($shipment, $request->user(), 'created', 'create', 'Kiriman dibuat; menunggu pemeriksaan Checker', 'Manifest dibuat.');
        }), 201);
    }

    public function show(Request $request, string $awb, OperationsWorkflow $workflow, VendorShipmentService $serializer): JsonResponse
    {
        $shipment = $workflow->visible($request->user(), 'admin')->where('tracking_number', $awb)
            ->with(['latestApproval', 'latestAssignment', 'courier', 'deliveryProof'])->firstOrFail();
        $inspection = $shipment->latestInspection;
        $safeInspection = $inspection ? $inspection->toArray() + ['photo_url' => $inspection->photo_path
            ? route('admin.inspection.photo', ['awb' => $awb]) : null, 'ocr_available' => false] : null;

        return response()->json(['data' => $serializer->serialize($shipment) + [
            'can_simulate' => $shipment->is_demo && in_array($shipment->status, ['in_transit', 'arrived'], true)
                && (app()->environment(['local', 'testing']) || config('services.pharma.simulator_enabled')),
            'inspection_detail' => $safeInspection, 'approval' => $shipment->latestApproval,
            'assignment' => $shipment->latestAssignment, 'courier' => $shipment->courier?->only(['id', 'name']),
            'open_issues_count' => $shipment->issues()->where('status', 'open')->count(),
            'proof' => $shipment->deliveryProof?->only(['id', 'mode', 'recipient_name', 'temperature', 'seal_intact', 'handed_over_at']),
            'report_ids' => $shipment->reports()->pluck('id'),
        ]])->header('Cache-Control', 'private, no-store');
    }

    public function cancel(Request $request, string $awb, OperationsWorkflow $workflow): JsonResponse
    {
        $data = $workflow->validateAction($request, ['reason' => ['required', 'string', 'min:5', 'max:4000']]);

        return response()->json($workflow->perform($request->user(), 'admin', $awb, 'cancel', $data, function (Shipment $s, User $actor) use ($data, $workflow) {
            abort_unless(in_array($s->status, ['inspection', 'pending_approval', 'approved', 'assigned', 'held'], true)
                && ! $s->issues()->where('status', 'open')->exists(), 409, 'Kiriman sudah ditangani; gunakan penanganan kendala.');
            $from = $s->status;
            $previousCourier = $s->courier_id;
            $s->latestAssignment?->update(['status' => 'cancelled', 'reason' => $data['reason']]);
            $s->courier_id = null;
            $s->status = 'cancelled';

            return $workflow->result($s, $actor, $from, 'cancel', 'Kiriman dibatalkan sebelum pengantaran', $data['reason'], ['notify_courier_id' => $previousCourier]);
        }));
    }

    public function inspectionPhoto(Request $request, string $awb, OperationsWorkflow $workflow): StreamedResponse
    {
        $s = $workflow->visible($request->user(), 'admin')->where('tracking_number', $awb)->firstOrFail();
        $path = $s->latestInspection?->photo_path;
        abort_unless($path && str_starts_with($path, 'inspections/') && ! str_contains($path, '..') && ! str_contains($path, '\\')
            && Storage::disk('local')->exists($path), 404);
        abort_unless(in_array(Storage::disk('local')->mimeType($path), ['image/png', 'image/jpeg', 'image/webp'], true), 404);

        return Storage::disk('local')->response($path, null, ['Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff']);
    }
}
