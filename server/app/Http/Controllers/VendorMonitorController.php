<?php

namespace App\Http\Controllers;

use App\CourierEvidenceStore;
use App\Models\ShipmentReport;
use App\VendorReportService;
use App\VendorShipmentService;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class VendorMonitorController extends Controller
{
    public function proof(Request $request, string $awb, VendorShipmentService $service): JsonResponse
    {
        $s = $service->find($request->user(), $awb);
        $pod = $s->deliveryProof;
        abort_unless($pod, 404, 'Bukti serah terima belum tersedia.');

        return response()->json(['data' => $pod->only(['id', 'mode', 'recipient_name', 'recipient_department',
            'temperature', 'seal_intact', 'handed_over_at', 'photo_sha256']) + [
                'digital_signature_verified' => false,
                'photo_url' => route('vendor.proof.photo', ['awb' => $awb]),
                'signature_url' => $pod->signature_path ? route('vendor.proof.signature', ['awb' => $awb]) : null,
            ]])->header('Cache-Control', 'private, no-store');
    }

    public function proofPhoto(Request $request, string $awb, VendorShipmentService $service, CourierEvidenceStore $store): Response
    {
        $pod = $service->find($request->user(), $awb)->deliveryProof;
        abort_unless($pod, 404, 'Bukti belum tersedia.');

        return $store->response($pod->photo_path);
    }

    public function proofSignature(Request $request, string $awb, VendorShipmentService $service, CourierEvidenceStore $store): Response
    {
        $pod = $service->find($request->user(), $awb)->deliveryProof;
        abort_unless($pod?->signature_path, 404, 'Tanda tangan belum tersedia.');

        return $store->response($pod->signature_path);
    }

    public function show(Request $request, string $awb, VendorShipmentService $service): JsonResponse
    {
        $shipment = $service->find($request->user(), $awb);
        $shipment->load('courier');

        return response()->json(['data' => $service->serialize($shipment) + [
            'courier' => $shipment->courier?->only(['id', 'name']),
            'open_issues_count' => $shipment->issues()->where('status', 'open')->count(),
            'available_reports_count' => $shipment->reports()->count(),
            'links' => [
                'telemetry' => route('vendor.telemetry', ['awb' => $awb]),
                'events' => route('vendor.events', ['awb' => $awb]),
                'reports' => route('vendor.reports', ['awb' => $awb]),
            ],
        ]])->header('Cache-Control', 'private, no-store');
    }

    public function telemetry(Request $request, string $awb, VendorShipmentService $service): JsonResponse
    {
        $shipment = $service->find($request->user(), $awb);
        $filters = $request->validate([
            'from' => ['sometimes', 'date'],
            'to' => ['sometimes', 'date', 'after_or_equal:from'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:1000'],
            'page' => ['sometimes', 'integer', 'min:1', 'max:100000'],
        ]);
        $query = $shipment->telemetry();
        if (! empty($filters['from'])) {
            $query->where('recorded_at', '>=', CarbonImmutable::parse($filters['from'])->utc());
        }
        if (! empty($filters['to'])) {
            $query->where('recorded_at', '<=', CarbonImmutable::parse($filters['to'])->utc());
        }
        $page = $query->orderByDesc('recorded_at')->orderByDesc('id')->paginate($filters['per_page'] ?? 300);
        $latest = $shipment->latestTelemetry;

        return response()->json([
            'data' => $page->getCollection()->reverse()->values()->map(fn ($sample) => [
                'id' => $sample->id, 'temperature' => $sample->temperature,
                'latitude' => $sample->latitude, 'longitude' => $sample->longitude,
                'speed_kmh' => $sample->speed_kmh, 'is_simulated' => $sample->is_simulated,
                'recorded_at' => $sample->recorded_at->toISOString(),
            ]),
            'meta' => [
                'current_page' => $page->currentPage(), 'last_page' => $page->lastPage(), 'total' => $page->total(),
                'per_page' => $page->perPage(), 'latest_recorded_at' => $latest?->recorded_at->toISOString(),
                'is_stale' => ! $latest || ! $latest->recorded_at->between(now()->subMinutes(15), now()->addMinute()),
                'temperature_min' => $shipment->temperature_min, 'temperature_max' => $shipment->temperature_max,
            ],
        ])->header('Cache-Control', 'private, no-store');
    }

    public function events(Request $request, string $awb, VendorShipmentService $service): JsonResponse
    {
        $shipment = $service->find($request->user(), $awb);
        $filters = $request->validate(['per_page' => ['sometimes', 'integer', 'min:1', 'max:100'], 'page' => ['sometimes', 'integer', 'min:1', 'max:100000']]);
        $page = $shipment->events()->orderByDesc('created_at')->orderByDesc('id')->paginate($filters['per_page'] ?? 20);

        return response()->json([
            'data' => $page->getCollection()->map(fn ($event) => $event->only(['id', 'type', 'title', 'reason', 'from_status', 'to_status']) + [
                'created_at' => $event->created_at->toISOString(),
            ]),
            'meta' => ['current_page' => $page->currentPage(), 'last_page' => $page->lastPage(), 'total' => $page->total()],
        ])->header('Cache-Control', 'private, no-store');
    }

    public function issues(Request $request, string $awb, VendorShipmentService $service): JsonResponse
    {
        $shipment = $service->find($request->user(), $awb);
        $filters = $request->validate(['per_page' => ['sometimes', 'integer', 'min:1', 'max:100'], 'page' => ['sometimes', 'integer', 'min:1', 'max:100000']]);
        $page = $shipment->issues()->orderByDesc('id')->paginate($filters['per_page'] ?? 20);

        return response()->json([
            'data' => $page->getCollection()->map(fn ($issue) => $issue->only(['id', 'type', 'status', 'description', 'resolution_reason']) + ['created_at' => $issue->created_at->toISOString()]),
            'meta' => ['current_page' => $page->currentPage(), 'last_page' => $page->lastPage(), 'total' => $page->total()],
        ])->header('Cache-Control', 'private, no-store');
    }

    public function reports(Request $request, string $awb, VendorShipmentService $service): JsonResponse
    {
        $shipment = $service->find($request->user(), $awb);
        $filters = $request->validate(['per_page' => ['sometimes', 'integer', 'min:1', 'max:100'], 'page' => ['sometimes', 'integer', 'min:1', 'max:100000']]);
        $page = $shipment->reports()->orderByDesc('revision')->paginate($filters['per_page'] ?? 20);

        return response()->json([
            'data' => $page->getCollection()->map(fn ($report) => [
                'id' => $report->id, 'title' => $report->title, 'revision' => $report->revision,
                'snapshot_sha256' => $report->sha256, 'is_demo' => $report->is_demo,
                'published_at' => $report->published_at->toISOString(),
                'download_url' => route('vendor.report.download', ['report' => $report->id]),
                'verify_url' => route('vendor.report.verify', ['report' => $report->id]),
            ]),
            'meta' => ['current_page' => $page->currentPage(), 'last_page' => $page->lastPage(), 'total' => $page->total()],
        ])->header('Cache-Control', 'private, no-store');
    }

    private function ownedReport(Request $request, int $report, VendorShipmentService $service): ShipmentReport
    {
        return ShipmentReport::whereIn('shipment_id', $service->owned($request->user())->select('id'))->findOrFail($report);
    }

    public function verify(Request $request, int $report, VendorShipmentService $service): JsonResponse
    {
        $record = $this->ownedReport($request, $report, $service);
        $valid = hash_equals($record->sha256, $record->snapshotHash());

        return response()->json(['data' => ['id' => $record->id, 'snapshot_sha256' => $record->sha256, 'integrity_valid' => $valid]])
            ->header('Cache-Control', 'private, no-store');
    }

    public function download(Request $request, int $report, VendorShipmentService $service, VendorReportService $pdf): Response
    {
        $record = $this->ownedReport($request, $report, $service);
        abort_unless(hash_equals($record->sha256, $record->snapshotHash()), 409, 'Integritas laporan tidak cocok. Hubungi Admin.');
        $snapshot = $record->snapshot;
        $content = '<p>'.($record->is_demo ? 'DATA DEMO — bukan serah terima nyata.' : 'Snapshot laporan pengiriman').'</p>';
        $content .= '<p>Resi: '.e($snapshot['tracking_number'] ?? '').' | Terbit: '.e($record->published_at->toISOString()).'</p>';
        $content .= '<p>SHA256 snapshot: '.e($record->sha256).'</p><pre style="white-space:pre-wrap;font-size:9px">'.e(json_encode($snapshot, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)).'</pre>';

        return $pdf->pdf($record->title, $content, 'laporan-'.$record->id.'.pdf');
    }

    public function inspectionPhoto(Request $request, string $awb, VendorShipmentService $service): StreamedResponse
    {
        $shipment = $service->find($request->user(), $awb);
        $path = $shipment->latestInspection?->photo_path;
        abort_unless($path && str_starts_with($path, 'inspections/') && ! str_contains($path, '..') && ! str_contains($path, '\\'), 404, 'Foto belum tersedia.');
        $disk = Storage::disk('local');
        abort_unless($disk->exists($path), 404, 'Foto belum tersedia.');
        $mime = $disk->mimeType($path);
        abort_unless(in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true), 404, 'Foto tidak valid.');

        return $disk->response($path, null, ['Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff']);
    }
}
