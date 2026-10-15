<?php

namespace App\Http\Controllers;

use App\CourierEvidenceStore;
use App\CourierTelemetryService;
use App\Models\Decision;
use App\Models\Shipment;
use App\Models\ShipmentIssue;
use App\Models\User;
use App\OperationsWorkflow;
use App\VendorShipmentService;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class AdminMonitorController extends Controller
{
    public function prediction(Request $request, string $awb, OperationsWorkflow $workflow, CourierTelemetryService $engine): JsonResponse
    {
        $s = $workflow->visible($request->user(), 'admin')->where('tracking_number', $awb)->firstOrFail();

        return response()->json(['data' => $engine->prediction($s)])->header('Cache-Control', 'private, no-store');
    }

    public function telemetry(Request $request, string $awb, OperationsWorkflow $workflow): JsonResponse
    {
        $s = $workflow->visible($request->user(), 'admin')->where('tracking_number', $awb)->firstOrFail();
        $data = $request->validate(['page' => ['sometimes', 'integer', 'between:1,100000'],
            'per_page' => ['sometimes', 'integer', 'between:1,1000'],
            'from' => ['sometimes', 'date'], 'to' => ['sometimes', 'date', 'after_or_equal:from']]);
        $query = $s->telemetry();
        if (isset($data['from'])) {
            $query->where('recorded_at', '>=', CarbonImmutable::parse($data['from'])->utc());
        }
        if (isset($data['to'])) {
            $query->where('recorded_at', '<=', CarbonImmutable::parse($data['to'])->utc());
        }
        $page = $query->orderByDesc('recorded_at')->orderByDesc('id')->paginate($data['per_page'] ?? 300);
        $latest = $s->latestTelemetry;

        return response()->json(['data' => $page->getCollection()->reverse()->values(), 'meta' => [
            'total' => $page->total(), 'current_page' => $page->currentPage(), 'last_page' => $page->lastPage(),
            'temperature_min' => $s->temperature_min, 'temperature_max' => $s->temperature_max,
            'is_stale' => ! $latest || ! $latest->recorded_at->between(now()->subMinutes(15), now()->addMinute())]])->header('Cache-Control', 'private, no-store');
    }

    public function issuePhoto(Request $request, int $issue, OperationsWorkflow $workflow, CourierEvidenceStore $store): Response
    {
        $workflow->authorize($request->user(), 'admin');
        $record = ShipmentIssue::findOrFail($issue);
        abort_unless($record->photo_path, 404);

        return $store->response($record->photo_path);
    }

    public function monitor(Request $request, OperationsWorkflow $workflow, VendorShipmentService $serializer): JsonResponse
    {
        $query = $workflow->visible($request->user(), 'admin');
        $data = $request->validate(['awb' => ['sometimes', 'string', 'max:100'], 'page' => ['sometimes', 'integer', 'between:1,100000']]);
        if (isset($data['awb'])) {
            $query->where('tracking_number', $data['awb']);
        } else {
            $query->whereIn('status', ['accepted', 'in_transit', 'arrived', 'returning', 'held', 'deviation_hold']);
        }
        $page = $query->with(['latestTelemetry', 'latestInspection.checker', 'courier'])->latest('id')->paginate(50);

        return response()->json(['data' => $page->getCollection()->map(fn ($s) => $serializer->serialize($s) + ['courier' => $s->courier?->only(['id', 'name'])]),
            'meta' => ['total' => $page->total(), 'current_page' => $page->currentPage(), 'last_page' => $page->lastPage()]])->header('Cache-Control', 'private, no-store');
    }

    public function issues(Request $request, OperationsWorkflow $workflow): JsonResponse
    {
        $workflow->authorize($request->user(), 'admin');
        $data = $request->validate(['awb' => ['sometimes', 'string', 'max:100'], 'status' => ['sometimes', Rule::in(['open', 'resolved'])],
            'page' => ['sometimes', 'integer', 'between:1,100000']]);
        $query = ShipmentIssue::with('latestDecision.admin');
        if (isset($data['awb'])) {
            $query->whereIn('shipment_id', Shipment::where('tracking_number', $data['awb'])->select('id'));
        }
        if (isset($data['status'])) {
            $query->where('status', $data['status']);
        }

        return response()->json($query->latest('id')->paginate(30))->header('Cache-Control', 'private, no-store');
    }

    public function decide(Request $request, string $awb, int $issue, OperationsWorkflow $workflow): JsonResponse
    {
        $data = $workflow->validateAction($request, ['action' => ['required', Rule::in(['resume', 'recheck', 'return'])], 'reason' => ['required', 'string', 'min:5', 'max:4000']]);
        $data['issue_id'] = $issue;

        return response()->json($workflow->perform($request->user(), 'admin', $awb, 'decide', $data, function (Shipment $s, User $actor) use ($data, $workflow) {
            abort_unless(in_array($s->status, ['held', 'deviation_hold'], true), 409, 'Kiriman tidak sedang ditahan.');
            $record = $s->issues()->whereKey($data['issue_id'])->lockForUpdate()->firstOrFail();
            abort_unless($record->status === 'open', 409, 'Kendala sudah diputuskan.');
            if ($data['action'] === 'resume') {
                $sample = $s->latestTelemetry;
                abort_unless($sample && $sample->recorded_at->between(now()->subMinutes(15), now()->addMinute())
                    && $sample->temperature !== null && $s->temperature_min !== null && $s->temperature_max !== null
                    && $sample->temperature >= $s->temperature_min && $sample->temperature <= $s->temperature_max
                    && ! in_array($record->type, ['seal', 'packaging'], true), 409, 'Belum aman untuk melanjutkan. Periksa ulang atau retur.');
                abort_if($s->issues()->where('status', 'open')->where('id', '!=', $record->id)->exists(), 409, 'Selesaikan kendala lain terlebih dahulu.');
            }
            $decision = Decision::create(['issue_id' => $record->id, 'user_id' => $actor->id, 'action' => $data['action'], 'reason' => $data['reason']]);
            $record->update(['status' => 'resolved', 'resolution_reason' => $data['reason']]);
            $from = $s->status;
            $previousCourier = $s->courier_id;
            if ($data['action'] === 'resume') {
                $s->status = in_array($record->previous_status, ['accepted', 'in_transit', 'arrived', 'returning'], true) ? $record->previous_status : 'in_transit';
            } elseif ($data['action'] === 'recheck') {
                abort_if($s->issues()->where('status', 'open')->exists(), 409, 'Selesaikan kendala lain terlebih dahulu.');
                $s->latestAssignment?->update(['status' => 'recheck', 'reason' => $data['reason']]);
                $s->courier_id = null;
                $s->status = 'inspection';
            } else {
                abort_unless($s->courier_id !== null, 409, 'Retur membutuhkan penugasan kurir.');
            }

            return $workflow->result($s, $actor, $from, 'decide', 'Keputusan kendala dicatat; kurir mengikuti instruksi Admin', $data['reason'], ['decision_id' => $decision->id, 'notify_courier_id' => $previousCourier]);
        }));
    }
}
