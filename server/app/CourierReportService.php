<?php

namespace App;

use App\Models\Shipment;
use App\Models\ShipmentReport;
use Illuminate\Http\Response;

class CourierReportService
{
    public function publish(Shipment $shipment): ShipmentReport
    {
        $shipment->loadMissing(['deliveryProof', 'latestInspection.checker']);
        $summary = $shipment->telemetry()->whereNotNull('temperature')->selectRaw('COUNT(*) AS count, MIN(temperature) AS min, MAX(temperature) AS max, AVG(temperature) AS average')->first();
        $snapshot = [
            'tracking_number' => $shipment->tracking_number, 'status' => $shipment->status, 'is_demo' => $shipment->is_demo,
            'sender_name' => $shipment->sender_name, 'recipient_name' => $shipment->recipient_name,
            'item_summary' => $shipment->item_summary, 'package_count' => $shipment->package_count,
            'temperature_min' => $shipment->temperature_min, 'temperature_max' => $shipment->temperature_max,
            'completed_at' => $shipment->completed_at?->toISOString(),
            'inspection' => ($shipment->latestInspection?->only(['packaging', 'seal', 'label', 'passed', 'temperature', 'note']) ?? [])
                + ['checker_name' => $shipment->latestInspection?->checker?->name],
            'temperature_summary' => ['count' => (int) $summary->count, 'min' => $summary->min, 'max' => $summary->max, 'average' => $summary->average],
            'telemetry_preview' => $shipment->telemetry()->orderByDesc('recorded_at')->limit(2000)->get()->reverse()->values()
                ->map(fn ($sample) => $sample->only(['temperature', 'latitude', 'longitude', 'is_simulated']) + ['recorded_at' => $sample->recorded_at->toISOString()])->all(),
            'telemetry_preview_limited' => $shipment->telemetry()->count() > 2000,
            'incidents' => $shipment->issues()->get(['type', 'description', 'status', 'resolution_reason', 'created_at'])->toArray(),
            'events' => $shipment->events()->orderBy('id')->get(['type', 'title', 'reason', 'from_status', 'to_status', 'created_at'])->toArray(),
            'pod' => $shipment->deliveryProof?->only(['mode', 'recipient_name', 'recipient_department', 'recipient_identity',
                'temperature', 'seal_intact', 'latitude', 'longitude', 'handed_over_at', 'photo_sha256']),
            'digital_signature_verified' => false,
        ];

        return ShipmentReport::create(['shipment_id' => $shipment->id, 'title' => 'Laporan Serah Terima Produk Farmasi',
            'revision' => 1, 'snapshot' => $snapshot, 'sha256' => ShipmentReport::hashSnapshot($snapshot),
            'is_demo' => $shipment->is_demo, 'published_at' => now()]);
    }

    public function download(ShipmentReport $report): Response
    {
        abort_unless(hash_equals($report->sha256, $report->snapshotHash()), 409, 'Integritas laporan tidak cocok.');
        $s = $report->snapshot;
        $html = '<p>'.($report->is_demo ? 'DATA DEMO — bukan sertifikat kepatuhan.' : 'Laporan pengiriman aplikasi.').'</p>';
        $html .= '<h2>Informasi pengiriman</h2><table>';
        foreach (['tracking_number' => 'Resi', 'item_summary' => 'Muatan', 'sender_name' => 'Pengirim', 'recipient_name' => 'Tujuan', 'status' => 'Status', 'completed_at' => 'Waktu selesai'] as $key => $label) {
            $html .= '<tr><th>'.e($label).'</th><td>'.e($s[$key] ?? 'Belum dicatat').'</td></tr>';
        }
        $html .= '</table><h2>Pemeriksaan awal</h2><p>'.e($s['inspection']['note'] ?? 'Belum tersedia').'</p>';
        $html .= '<h2>Monitoring suhu</h2><p>Rentang konfigurasi: '.e($s['temperature_min'] ?? '—').' sampai '.e($s['temperature_max'] ?? '—').' °C. Sampel: '.e($s['temperature_summary']['count'] ?? 0).'.</p>';
        $html .= '<p>Minimum '.e($s['temperature_summary']['min'] ?? '—').' / maksimum '.e($s['temperature_summary']['max'] ?? '—').' °C.</p>';
        $html .= '<h2>Kendala dan keputusan</h2>';
        foreach ($s['incidents'] ?? [] as $issue) {
            $html .= '<p><strong>'.e($issue['type']).'</strong>: '.e($issue['description']).' — '.e($issue['resolution_reason'] ?? 'Belum ada alasan resolusi').'</p>';
        }
        $html .= '<h2>Bukti serah terima</h2><table>';
        foreach ($s['pod'] ?? [] as $key => $value) {
            $html .= '<tr><th>'.e(str_replace('_', ' ', $key)).'</th><td>'.e(is_bool($value) ? ($value ? 'Ya' : 'Tidak') : ($value ?? 'Belum dicatat')).'</td></tr>';
        }
        $html .= '</table><h2>Linimasa audit</h2><table>';
        foreach ($s['events'] ?? [] as $event) {
            $html .= '<tr><td>'.e($event['created_at']).'</td><td>'.e($event['title']).'</td><td>'.e($event['reason'] ?? '').'</td></tr>';
        }
        $html .= '</table><p>SHA256 snapshot: '.e($report->sha256).'</p><p>Checksum bukan enkripsi atau tanda tangan digital tersertifikasi.</p>';

        return app(VendorReportService::class)->pdf($report->title, $html, 'BAP-'.$report->id.'-R'.$report->revision.'.pdf');
    }
}
