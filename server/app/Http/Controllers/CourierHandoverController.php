<?php

namespace App\Http\Controllers;

use App\CourierEvidenceStore;
use App\CourierReportService;
use App\CourierShipmentService;
use App\CourierWorkflow;
use App\Models\Decision;
use App\Models\DeliveryProof;
use App\Models\Shipment;
use App\Models\ShipmentReport;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Throwable;

class CourierHandoverController extends Controller
{
    public function show(Request $request, string $awb, CourierShipmentService $service): JsonResponse
    {
        $shipment = $service->find($request->user(), $awb);
        $proof = $shipment->deliveryProof;
        $report = $shipment->reports()->orderByDesc('revision')->first();

        return response()->json(['data' => ['task' => $service->task($shipment),
            'proof' => $proof ? $proof->only(['id', 'mode', 'recipient_name', 'recipient_department', 'temperature',
                'seal_intact', 'latitude', 'longitude', 'photo_sha256']) + [
                    'handed_over_at' => $proof->handed_over_at->toISOString(),
                    'photo_url' => route('courier.proof.photo', ['awb' => $awb]),
                    'signature_url' => $proof->signature_path ? route('courier.proof.signature', ['awb' => $awb]) : null,
                    'digital_signature_verified' => false,
                ] : null,
            'report' => $report ? ['id' => $report->id, 'revision' => $report->revision, 'snapshot_sha256' => $report->sha256,
                'download_url' => route('courier.report.download', ['report' => $report->id]), 'published_at' => $report->published_at->toISOString()] : null,
            'can_submit' => in_array($shipment->status, ['arrived', 'returning'], true) && ! $shipment->issues()->where('status', 'open')->exists(),
        ]])->header('Cache-Control', 'private, no-store');
    }

    public function store(Request $request, string $awb, CourierWorkflow $workflow, CourierEvidenceStore $evidence, CourierReportService $reports): JsonResponse
    {
        $data = $request->validate([
            'version' => ['required', 'integer', 'min:1'], 'request_id' => ['required', 'uuid'],
            'mode' => ['required', Rule::in(['normal', 'return'])],
            'recipient_name' => ['required', 'string', 'min:2', 'max:255'],
            'recipient_department' => ['sometimes', 'nullable', 'string', 'max:255'],
            'recipient_identity' => ['sometimes', 'nullable', 'string', 'max:100'],
            'temperature' => ['required', 'numeric', 'between:-100,100'], 'seal_intact' => ['required', 'boolean'],
            'latitude' => ['required', 'numeric', 'between:-90,90'], 'longitude' => ['required', 'numeric', 'between:-180,180'],
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'signature' => ['sometimes', 'image', 'mimes:png,jpg,jpeg', 'max:1024'],
        ]);
        $paths = [];
        try {
            $result = $workflow->perform($request->user(), $awb, 'handover', $data, function (Shipment $shipment, User $courier) use ($data, $workflow, $evidence, $reports, &$paths) {
                $expected = $data['mode'] === 'return' ? 'returning' : 'arrived';
                abort_unless($shipment->status === $expected && $shipment->latestAssignment?->status === 'accepted', 409, 'Tahap pengiriman tidak sesuai mode serah terima.');
                abort_if($shipment->issues()->where('status', 'open')->exists(), 409, 'Admin belum menyelesaikan kendala.');
                if ($data['mode'] === 'normal') {
                    $workflow->approved($shipment);
                    abort_unless((bool) $data['seal_intact'] && $shipment->temperature_min !== null && $shipment->temperature_max !== null
                        && $data['temperature'] >= $shipment->temperature_min && $data['temperature'] <= $shipment->temperature_max, 422, 'Segel/suhu tidak aman. Laporkan kendala sebelum serah terima.');
                } else {
                    $decision = Decision::whereIn('issue_id', $shipment->issues()->select('id'))->with('admin')->latest('id')->first();
                    abort_unless($decision && $decision->action === 'return' && $decision->admin?->role === 'admin', 409, 'Retur tidak memiliki keputusan Admin.');
                }
                $photo = $evidence->store($data['photo']);
                $paths[] = $photo['path'];
                $signature = isset($data['signature']) ? $evidence->store($data['signature']) : null;
                if ($signature) {
                    $paths[] = $signature['path'];
                }
                $proof = DeliveryProof::create([
                    'shipment_id' => $shipment->id, 'courier_id' => $courier->id, 'mode' => $data['mode'],
                    'recipient_name' => $data['recipient_name'], 'recipient_department' => $data['recipient_department'] ?? null,
                    'recipient_identity' => $data['recipient_identity'] ?? null, 'temperature' => $data['temperature'],
                    'seal_intact' => (bool) $data['seal_intact'], 'latitude' => $data['latitude'], 'longitude' => $data['longitude'],
                    'photo_path' => $photo['path'], 'photo_sha256' => $photo['sha256'], 'signature_path' => $signature['path'] ?? null,
                    'handed_over_at' => now(),
                ]);
                $from = $shipment->status;
                $shipment->status = $data['mode'] === 'return' ? 'returned' : 'completed';
                $shipment->completed_at = now();
                $shipment->version++;
                $shipment->save();
                $event = $workflow->record($shipment, $courier, $data['mode'] === 'return' ? 'return_complete' : 'deliver',
                    'Serah terima selesai dan kiriman dikunci', $from, ['proof_id' => $proof->id, 'photo_sha256' => $photo['sha256']]);
                $workflow->notify($shipment, $event);
                $report = $reports->publish($shipment);

                return ['workflow_status' => $shipment->status, 'version' => $shipment->version, 'proof_id' => $proof->id,
                    'report_id' => $report->id, 'download_url' => route('courier.report.download', ['report' => $report->id]), 'notification_status' => 'queued'];
            });

            return response()->json($result);
        } catch (Throwable $exception) {
            foreach ($paths as $path) {
                $evidence->remove($path);
            }
            throw $exception;
        }
    }

    public function download(Request $request, int $report, CourierShipmentService $service, CourierReportService $reports): Response
    {
        $record = ShipmentReport::whereIn('shipment_id', $service->owned($request->user())->select('id'))->findOrFail($report);

        return $reports->download($record);
    }

    public function photo(Request $request, string $awb, CourierShipmentService $service, CourierEvidenceStore $evidence): Response
    {
        $proof = $service->find($request->user(), $awb)->deliveryProof;
        abort_unless($proof, 404, 'Bukti belum tersedia.');

        return $evidence->response($proof->photo_path);
    }

    public function signature(Request $request, string $awb, CourierShipmentService $service, CourierEvidenceStore $evidence): Response
    {
        $proof = $service->find($request->user(), $awb)->deliveryProof;
        abort_unless($proof?->signature_path, 404, 'Tanda tangan belum tersedia.');

        return $evidence->response($proof->signature_path);
    }

    public function shareSummary(Request $request, string $awb, CourierShipmentService $service): JsonResponse
    {
        $shipment = $service->find($request->user(), $awb);
        $proof = $shipment->deliveryProof;
        abort_unless($proof, 409, 'Serah terima belum selesai.');

        return response()->json(['data' => [
            'text' => 'Laporan tugas: '.$shipment->tracking_number.' | '.$shipment->status.' | '.$proof->handed_over_at->toISOString(),
            'dispatcher_phone' => config('services.pharma.dispatcher_phone'),
            'automatically_sent' => false,
        ]])->header('Cache-Control', 'private, no-store');
    }
}
