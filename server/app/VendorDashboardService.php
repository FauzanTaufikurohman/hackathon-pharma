<?php

namespace App;

use App\Models\ShipmentIssue;
use App\Models\ShipmentReport;
use App\Models\TelemetrySample;
use App\Models\User;

class VendorDashboardService
{
    public function dashboard(User $user, VendorShipmentService $service, ?string $awb = null): array
    {
        $owned = $service->owned($user);
        $active = (clone $owned)->whereNotIn('status', ['completed', 'delivered', 'returned']);
        $latest = TelemetrySample::query()
            ->join('shipments', 'shipments.id', '=', 'telemetry_samples.shipment_id')
            ->where('shipments.buyer_id', $user->id)
            ->whereNotIn('shipments.status', ['completed', 'delivered', 'returned'])
            ->where('shipments.temperature_profile', 'controlled')
            ->whereNotNull('shipments.temperature_min')->whereNotNull('shipments.temperature_max')
            ->where('telemetry_samples.recorded_at', '>=', now()->subMinutes(15))
            ->where('telemetry_samples.recorded_at', '<=', now()->addMinute())
            ->whereNotNull('telemetry_samples.temperature')
            ->whereRaw('telemetry_samples.id = (SELECT t.id FROM telemetry_samples t WHERE t.shipment_id = shipments.id ORDER BY t.recorded_at DESC, t.id DESC LIMIT 1)')
            ->selectRaw('COUNT(*) AS measured, AVG(telemetry_samples.temperature) AS average_temp,
                SUM(CASE WHEN telemetry_samples.temperature BETWEEN shipments.temperature_min AND shipments.temperature_max THEN 1 ELSE 0 END) AS safe,
                SUM(CASE WHEN telemetry_samples.is_simulated THEN 1 ELSE 0 END) AS simulated')->first();
        $reports = ShipmentReport::whereIn('shipment_id', (clone $owned)->select('id'));
        $latestReport = (clone $reports)->orderByDesc('published_at')->orderByDesc('id')->first();
        $arrival = (clone $active)->whereNotNull('scheduled_at')->orderBy('scheduled_at')->orderBy('id')->first();
        $priority = $awb ? $service->find($user, $awb) : (clone $active)
            ->orderByRaw("CASE WHEN status IN ('deviation_hold', 'held', 'rejected') THEN 0 ELSE 1 END")
            ->orderByRaw('CASE WHEN scheduled_at IS NULL THEN 1 ELSE 0 END')->orderBy('scheduled_at')->orderBy('id')->first();

        return [
            'facility' => $user->facility,
            'metrics' => [
                'active_shipments_count' => (clone $active)->count(),
                'critical_count' => (clone $owned)->whereIn('status', ['deviation_hold', 'held', 'rejected'])->count(),
                'average_temperature_str' => $latest->measured ? number_format((float) $latest->average_temp, 1).'°C' : 'Belum ada data sensor',
                'compliance_rate' => $latest->measured ? round(100 * (int) $latest->safe / (int) $latest->measured, 1) : null,
                'measured_shipments_count' => (int) $latest->measured,
                'simulated_measurements_count' => (int) $latest->simulated,
                'compliance_scope' => 'latest_fresh_samples_not_certification',
                'incident_count' => ShipmentIssue::whereIn('shipment_id', (clone $owned)->select('id'))->where('status', 'open')->count(),
                'available_reports_count' => (clone $reports)->count(),
                'digital_hash' => $latestReport?->sha256,
                'next_arrival_time' => $arrival ? $arrival->scheduled_at->timezone('Asia/Jakarta')->format('d M Y, H:i').' WIB' : null,
                'next_arrival_item' => $arrival?->item_summary,
            ],
            'priority_shipment' => $priority ? $service->serialize($priority) : null,
            'counts' => $service->counts($user),
            'refreshed_at' => now()->toISOString(),
        ];
    }
}
