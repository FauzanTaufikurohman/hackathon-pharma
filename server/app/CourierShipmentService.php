<?php

namespace App;

use App\Models\Shipment;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class CourierShipmentService
{
    public const BUCKETS = [
        'assigned' => ['assigned'],
        'in_transit' => ['accepted', 'in_transit', 'arrived', 'returning'],
        'hold' => ['held', 'deviation_hold'],
        'delivered' => ['delivered', 'completed', 'returned'],
    ];

    public function owned(User $user): Builder
    {
        abort_unless($user->role === 'courier' && $user->is_active && $user->tokenCan('courier'), 403, 'Akses ini khusus kurir aktif.');

        return Shipment::where('courier_id', $user->id);
    }

    public function find(User $user, string $awb): Shipment
    {
        return $this->owned($user)->where('tracking_number', $awb)->firstOrFail();
    }

    public function counts(User $user): array
    {
        $totals = $this->owned($user)->selectRaw('status, COUNT(*) AS total')->groupBy('status')->pluck('total', 'status');
        $result = ['all' => (int) $totals->sum()];
        foreach (self::BUCKETS as $bucket => $statuses) {
            $result[$bucket] = (int) collect($statuses)->sum(fn ($s) => (int) ($totals[$s] ?? 0));
        }

        return $result;
    }

    public function task(Shipment $shipment): array
    {
        $shipment->loadMissing(['latestTelemetry', 'latestInspection.checker', 'latestAssignment', 'deliveryProof']);
        $sample = $shipment->latestTelemetry;
        $bucket = collect(self::BUCKETS)->search(fn ($states) => in_array($shipment->status, $states, true));
        $cta = $bucket === 'delivered' ? 'view_pod' : ($shipment->status === 'assigned' ? 'accept_reject' : 'monitor');
        $label = VendorShipmentService::LABELS[$shipment->status] ?? $shipment->status;

        return [
            'id' => $shipment->id, 'awb' => $shipment->tracking_number,
            'status' => $bucket ?: 'hold', 'workflow_status' => $shipment->status, 'status_label' => $label,
            'version' => $shipment->version, 'assignment_id' => $shipment->latestAssignment?->id,
            'category_label' => $shipment->temperature_profile === 'controlled' ? 'Suhu terkontrol' : 'Reguler',
            'temperature_profile_label' => $shipment->temperature_min !== null ? $shipment->temperature_min.'°C – '.$shipment->temperature_max.'°C' : 'Belum dikonfigurasi',
            'item_title' => $shipment->item_summary, 'batch_number' => $shipment->batch_number,
            'probe_temp_str' => $sample?->temperature !== null ? $sample->temperature.'°C' : 'Belum ada data',
            'seal_str' => $shipment->latestInspection ? ($shipment->latestInspection->seal ? 'Utuh saat pemeriksaan' : 'Rusak saat pemeriksaan') : 'Belum diperiksa',
            'battery_pct' => $sample?->battery_pct,
            'origin_name' => $shipment->sender_name, 'origin_time' => null,
            'destination_name' => $shipment->recipient_name, 'destination_eta' => $shipment->scheduled_at?->toISOString(),
            'destination_address' => $shipment->address, 'cooler_box_desc' => $shipment->package_type,
            'tolerance_label' => $shipment->handling_instructions, 'route_progress_note' => $shipment->last_location,
            'recipient_person' => $shipment->deliveryProof?->recipient_name,
            'pod_number' => $shipment->deliveryProof ? 'POD-'.$shipment->deliveryProof->id : null,
            'cta_label' => $cta === 'accept_reject' ? 'Tanggapi tugas' : ($cta === 'view_pod' ? 'Lihat laporan' : 'Buka monitor'),
            'cta_type' => $cta, 'hint_text' => $label, 'waybill_number' => $shipment->tracking_number,
            'weight_str' => $shipment->weight_kg.' kg', 'origin_detail' => $shipment->origin_city,
            'destination_detail' => $shipment->destination_city, 'scheduled_at_str' => $shipment->scheduled_at?->toISOString(),
            'commodity_name' => $shipment->item_summary, 'temp_min' => $shipment->temperature_min,
            'temp_max' => $shipment->temperature_max, 'temp_actual' => $sample?->temperature,
            'handling_instructions' => $shipment->handling_instructions,
            'inspector_name' => $shipment->latestInspection?->checker?->name, 'inspector_status' => $shipment->latestInspection?->passed,
            'is_demo' => $shipment->is_demo, 'telemetry_recorded_at' => $sample?->recorded_at->toISOString(),
            'telemetry_stale' => ! $sample || ! $sample->recorded_at->between(now()->subMinutes(15), now()->addMinute()),
        ];
    }

    public function profile(User $user): array
    {
        $this->owned($user);
        $profile = $user->courier_profile ?? [];
        $latest = $this->owned($user)->with('latestTelemetry')->whereIn('status', ['accepted', 'in_transit', 'arrived', 'returning'])
            ->orderByDesc('updated_at')->first()?->latestTelemetry;

        return [
            'id' => $user->id, 'courier_code' => $profile['courier_code'] ?? 'COUR-'.$user->id,
            'name' => $user->name, 'role_label' => 'Satria Pharma', 'shift_name' => $profile['shift_name'] ?? null,
            'hub_base' => $profile['hub_base'] ?? null, 'duty_active' => $user->is_active,
            'cooler_box_code' => $profile['cooler_box_code'] ?? null,
            'current_cooler_temp' => $latest?->temperature, 'ble_sync' => null,
            'battery_pct' => $latest?->battery_pct, 'avatar_url' => $profile['avatar_url'] ?? null,
            'capacity_kg' => $profile['capacity_kg'] ?? null,
        ];
    }
}
