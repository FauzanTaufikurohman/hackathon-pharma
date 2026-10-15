<?php

namespace App\Http\Controllers;

use App\CourierShipmentService;
use App\CourierTelemetryService;
use App\CourierWorkflow;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CourierDeliveryController extends Controller
{
    public function show(Request $request, string $awb, CourierShipmentService $service): JsonResponse
    {
        $shipment = $service->find($request->user(), $awb);

        return response()->json(['data' => [
            'task' => $service->task($shipment),
            'recipient' => $shipment->only(['recipient_name', 'recipient_phone', 'address', 'recipient_ready']),
            'open_issues_count' => $shipment->issues()->where('status', 'open')->count(),
            'can_depart' => $shipment->status === 'accepted',
            'can_arrive' => $shipment->status === 'in_transit' && ! $shipment->issues()->where('status', 'open')->exists(),
            'can_handover' => in_array($shipment->status, ['arrived', 'returning'], true)
                && ! $shipment->issues()->where('status', 'open')->exists(),
        ]])->header('Cache-Control', 'private, no-store');
    }

    public function depart(Request $request, string $awb, CourierWorkflow $workflow): JsonResponse
    {
        $data = $request->validate(['version' => ['required', 'integer', 'min:1'], 'request_id' => ['required', 'uuid']]);

        return response()->json($workflow->perform($request->user(), $awb, 'depart', $data, function (Shipment $shipment, User $courier) use ($workflow) {
            abort_unless($shipment->status === 'accepted' && $shipment->latestAssignment?->status === 'accepted', 409, 'Tugas harus diterima sebelum berangkat.');
            $workflow->approved($shipment);
            abort_if($shipment->issues()->where('status', 'open')->exists(), 409, 'Ada kendala yang belum diputuskan Admin.');
            $shipment->status = 'in_transit';
            $shipment->version++;
            $shipment->save();
            $event = $workflow->record($shipment, $courier, 'depart', 'Kurir memulai pengantaran', 'accepted');
            $workflow->notify($shipment, $event);

            return ['awb' => $shipment->tracking_number, 'workflow_status' => $shipment->status, 'version' => $shipment->version, 'notification_status' => 'queued'];
        }));
    }

    public function arrive(Request $request, string $awb, CourierWorkflow $workflow): JsonResponse
    {
        $data = $request->validate(['version' => ['required', 'integer', 'min:1'], 'request_id' => ['required', 'uuid'],
            'latitude' => ['sometimes', 'numeric', 'between:-90,90', 'required_with:longitude'],
            'longitude' => ['sometimes', 'numeric', 'between:-180,180', 'required_with:latitude']]);

        return response()->json($workflow->perform($request->user(), $awb, 'arrive', $data, function (Shipment $shipment, User $courier) use ($workflow, $data) {
            abort_unless($shipment->status === 'in_transit', 409, 'Konfirmasi tiba hanya saat pengantaran aktif.');
            abort_if($shipment->issues()->where('status', 'open')->exists(), 409, 'Kiriman memiliki kendala terbuka.');
            $shipment->status = 'arrived';
            $shipment->version++;
            $shipment->save();
            $event = $workflow->record($shipment, $courier, 'arrive', 'Kurir mengonfirmasi tiba', 'in_transit',
                array_intersect_key($data, array_flip(['latitude', 'longitude'])));
            $workflow->notify($shipment, $event);

            return ['awb' => $shipment->tracking_number, 'workflow_status' => 'arrived', 'version' => $shipment->version, 'arrived_at' => $event->created_at->toISOString()];
        }));
    }

    public function telemetry(Request $request, string $awb, CourierWorkflow $workflow, CourierTelemetryService $telemetry): JsonResponse
    {
        $data = $request->validate([
            'request_id' => ['required', 'uuid'], 'temperature' => ['required', 'numeric', 'between:-100,100'],
            'latitude' => ['required', 'numeric', 'between:-90,90'], 'longitude' => ['required', 'numeric', 'between:-180,180'],
            'speed_kmh' => ['sometimes', 'nullable', 'numeric', 'between:0,300'],
            'sensor_id' => ['sometimes', 'nullable', 'string', 'max:100'],
            'battery_pct' => ['sometimes', 'nullable', 'integer', 'between:0,100'],
            'recorded_at' => ['required', 'date', 'after_or_equal:'.now()->subDay()->toISOString(), 'before_or_equal:'.now()->addMinute()->toISOString()],
            'is_simulated' => ['prohibited'],
        ]);

        return response()->json($workflow->perform($request->user(), $awb, 'telemetry', $data,
            fn (Shipment $shipment) => $telemetry->append($shipment, $data)));
    }

    public function samples(Request $request, string $awb, CourierShipmentService $service): JsonResponse
    {
        $shipment = $service->find($request->user(), $awb);
        $filters = $request->validate(['per_page' => ['sometimes', 'integer', 'between:1,1000'], 'page' => ['sometimes', 'integer', 'between:1,100000']]);
        $page = $shipment->telemetry()->orderByDesc('recorded_at')->orderByDesc('id')->paginate($filters['per_page'] ?? 300);

        return response()->json(['data' => $page->getCollection()->reverse()->values()->map(fn ($s) => $s->only([
            'id', 'temperature', 'latitude', 'longitude', 'speed_kmh', 'sensor_id', 'battery_pct', 'is_simulated',
        ]) + ['recorded_at' => $s->recorded_at->toISOString()]),
            'meta' => ['total' => $page->total(), 'current_page' => $page->currentPage(), 'last_page' => $page->lastPage()],
        ])->header('Cache-Control', 'private, no-store');
    }
}
