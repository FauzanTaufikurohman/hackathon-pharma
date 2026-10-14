<?php

namespace App\Http\Controllers;

use App\CourierEvidenceStore;
use App\CourierShipmentService;
use App\CourierWorkflow;
use App\Models\Decision;
use App\Models\Shipment;
use App\Models\ShipmentIssue;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Throwable;

class CourierIssuesController extends Controller
{
    public function index(Request $request, string $awb, CourierShipmentService $service): JsonResponse
    {
        $shipment = $service->find($request->user(), $awb);
        $data = $request->validate(['per_page' => ['sometimes', 'integer', 'between:1,100'], 'page' => ['sometimes', 'integer', 'between:1,100000']]);
        $page = $shipment->issues()->with('latestDecision.admin')->orderByDesc('id')->paginate($data['per_page'] ?? 20);

        return response()->json(['data' => $page->getCollection()->map(function ($issue) {
            $decision = $issue->latestDecision;

            return $issue->only(['id', 'type', 'status', 'description', 'previous_status', 'resolution_reason', 'temperature', 'latitude', 'longitude'])
                + ['created_at' => $issue->created_at->toISOString(),
                    'photo_url' => $issue->photo_path ? route('courier.issue.photo', ['issue' => $issue->id]) : null,
                    'decision' => $decision?->admin?->role === 'admin' ? $decision->only(['id', 'action', 'reason'])
                        + ['admin_name' => $decision->admin->name, 'decided_at' => $decision->created_at->toISOString()] : null];
        }), 'meta' => ['total' => $page->total(), 'current_page' => $page->currentPage(), 'last_page' => $page->lastPage()]]);
    }

    public function store(Request $request, string $awb, CourierWorkflow $workflow, CourierEvidenceStore $evidence): JsonResponse
    {
        $data = $request->validate([
            'version' => ['required', 'integer', 'min:1'], 'request_id' => ['required', 'uuid'],
            'category' => ['required', Rule::in(['seal', 'temperature', 'packaging', 'recipient_unavailable', 'address', 'other'])],
            'description' => ['required', 'string', 'min:10', 'max:4000'],
            'latitude' => ['sometimes', 'numeric', 'between:-90,90', 'required_with:longitude'],
            'longitude' => ['sometimes', 'numeric', 'between:-180,180', 'required_with:latitude'],
            'photo' => ['sometimes', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);
        $paths = [];
        try {
            $result = $workflow->perform($request->user(), $awb, 'issue', $data, function (Shipment $shipment, User $courier) use ($data, $workflow, $evidence, &$paths) {
                abort_unless(in_array($shipment->status, ['accepted', 'in_transit', 'arrived', 'held', 'deviation_hold', 'returning'], true), 409, 'Kendala dilaporkan pada tugas yang sudah diterima.');
                $stored = isset($data['photo']) ? $evidence->store($data['photo']) : null;
                if ($stored) {
                    $paths[] = $stored['path'];
                }
                $from = $shipment->status;
                $sample = $shipment->latestTelemetry;
                $issue = ShipmentIssue::create([
                    'shipment_id' => $shipment->id, 'user_id' => $courier->id, 'type' => $data['category'],
                    'description' => $data['description'], 'previous_status' => $from, 'status' => 'open',
                    'latitude' => $data['latitude'] ?? $sample?->latitude,
                    'longitude' => $data['longitude'] ?? $sample?->longitude,
                    'temperature' => $sample?->temperature, 'photo_path' => $stored['path'] ?? null,
                ]);
                $shipment->status = 'deviation_hold';
                $shipment->version++;
                $shipment->save();
                $event = $workflow->record($shipment, $courier, 'issue', 'Kurir melaporkan kendala; kiriman ditahan', $from,
                    ['issue_id' => $issue->id, 'location_source' => isset($data['latitude']) ? 'submitted' : ($sample ? 'last_known' : 'unavailable')], $data['description']);
                $workflow->notify($shipment, $event);

                return ['issue_id' => $issue->id, 'workflow_status' => $shipment->status, 'version' => $shipment->version, 'notification_status' => 'queued'];
            });

            foreach ($paths as $path) {
                if (! ShipmentIssue::where('photo_path', $path)->exists()) {
                    $evidence->remove($path);
                }
            }

            return response()->json($result);
        } catch (Throwable $exception) {
            foreach ($paths as $path) {
                $evidence->remove($path);
            }
            throw $exception;
        }
    }

    public function confirmReturn(Request $request, string $awb, CourierWorkflow $workflow): JsonResponse
    {
        $data = $request->validate(['version' => ['required', 'integer', 'min:1'], 'request_id' => ['required', 'uuid']]);

        return response()->json($workflow->perform($request->user(), $awb, 'confirm_return', $data, function (Shipment $shipment, User $courier) use ($workflow) {
            $decision = Decision::whereIn('issue_id', $shipment->issues()->select('id'))->with('admin')->latest('id')->first();
            abort_unless(in_array($shipment->status, ['held', 'deviation_hold'], true) && $decision
                && $decision->action === 'return' && $decision->admin?->role === 'admin'
                && ! $shipment->issues()->where('status', 'open')->exists(), 409, 'Retur membutuhkan keputusan Admin yang valid dan tidak ada kendala terbuka.');
            $from = $shipment->status;
            $shipment->status = 'returning';
            $shipment->version++;
            $shipment->save();
            $event = $workflow->record($shipment, $courier, 'return_acknowledged', 'Kurir mengonfirmasi instruksi retur Admin', $from,
                ['decision_id' => $decision->id], $decision->reason);
            $workflow->notify($shipment, $event);

            return ['workflow_status' => 'returning', 'version' => $shipment->version];
        }));
    }

    public function photo(Request $request, int $issue, CourierShipmentService $service, CourierEvidenceStore $evidence): Response
    {
        $record = ShipmentIssue::whereIn('shipment_id', $service->owned($request->user())->select('id'))->findOrFail($issue);
        abort_unless($record->photo_path, 404, 'Foto tidak tersedia.');

        return $evidence->response($record->photo_path);
    }
}
