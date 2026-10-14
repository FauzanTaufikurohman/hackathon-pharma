<?php

namespace App\Http\Controllers;

use App\CourierShipmentService;
use App\CourierWorkflow;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CourierIncomingController extends Controller
{
    public function show(Request $request, string $awb, CourierShipmentService $service): JsonResponse
    {
        return response()->json(['data' => $service->task($service->find($request->user(), $awb))])->header('Cache-Control', 'private, no-store');
    }

    public function accept(Request $request, string $awb, CourierWorkflow $workflow): JsonResponse
    {
        $data = $request->validate([
            'version' => ['required', 'integer', 'min:1'], 'request_id' => ['required', 'uuid'],
            'pre_trip' => ['required', 'array:seal,icepack,manifest,temperature'],
            'pre_trip.seal' => ['required', 'accepted'], 'pre_trip.icepack' => ['required', 'accepted'],
            'pre_trip.manifest' => ['required', 'accepted'], 'pre_trip.temperature' => ['required', 'numeric', 'between:-100,100'],
        ]);
        $result = $workflow->perform($request->user(), $awb, 'accept', $data, function (Shipment $shipment, User $courier) use ($data, $workflow) {
            $assignment = $shipment->latestAssignment;
            abort_unless($shipment->status === 'assigned' && $assignment && $assignment->courier_id === $courier->id && $assignment->status === 'pending', 409, 'Tugas tidak menunggu konfirmasi kurir ini.');
            $workflow->approved($shipment);
            $temp = (float) $data['pre_trip']['temperature'];
            abort_unless($shipment->temperature_min !== null && $shipment->temperature_max !== null
                && $temp >= $shipment->temperature_min && $temp <= $shipment->temperature_max, 422, 'Suhu pra-jalan berada di luar rentang kiriman.');
            $capacity = (float) ($courier->courier_profile['capacity_kg'] ?? 0);
            $loaded = Shipment::where('courier_id', $courier->id)->whereIn('status', ['accepted', 'in_transit', 'arrived', 'held', 'deviation_hold', 'returning'])->sum('weight_kg');
            abort_unless($capacity > 0 && $loaded + $shipment->weight_kg <= $capacity, 409, 'Kapasitas cold-box belum dikonfigurasi atau tidak mencukupi.');
            $assignment->update(['status' => 'accepted', 'pre_trip' => $data['pre_trip'], 'responded_at' => now()]);
            $shipment->status = 'accepted';
            $shipment->version++;
            $shipment->save();
            $event = $workflow->record($shipment, $courier, 'accept', 'Tugas diterima kurir', 'assigned', ['pre_trip' => $data['pre_trip']]);
            $workflow->notify($shipment, $event);

            return ['awb' => $shipment->tracking_number, 'workflow_status' => 'accepted', 'version' => $shipment->version, 'notification_status' => 'queued'];
        });

        return response()->json($result);
    }

    public function reject(Request $request, string $awb, CourierWorkflow $workflow): JsonResponse
    {
        $data = $request->validate(['version' => ['required', 'integer', 'min:1'], 'request_id' => ['required', 'uuid'],
            'category' => ['required', 'string', 'max:80'], 'notes' => ['required', 'string', 'min:5', 'max:2000']]);

        return response()->json($workflow->perform($request->user(), $awb, 'reject', $data, function (Shipment $shipment, User $courier) use ($data, $workflow) {
            $assignment = $shipment->latestAssignment;
            abort_unless($shipment->status === 'assigned' && $assignment && $assignment->courier_id === $courier->id && $assignment->status === 'pending', 409, 'Tugas tidak dapat ditolak pada tahap ini.');
            $assignment->update(['status' => 'rejected', 'reason' => $data['category'].': '.$data['notes'], 'responded_at' => now()]);
            $shipment->status = 'approved';
            $shipment->courier_id = null;
            $shipment->version++;
            $shipment->save();
            $event = $workflow->record($shipment, $courier, 'reject', 'Penugasan ditolak; menunggu Admin menugaskan ulang', 'assigned', [], $assignment->reason);
            $workflow->notify($shipment, $event, $courier->id);

            return ['awb' => $shipment->tracking_number, 'workflow_status' => 'approved', 'version' => $shipment->version, 'notification_status' => 'queued'];
        }));
    }
}
