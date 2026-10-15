<?php

namespace App;

use App\Models\Shipment;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class VendorShipmentService
{
    public const CATEGORY_STATUSES = [
        'transit' => ['assigned', 'accepted', 'in_transit', 'arrived', 'returning'],
        'qc' => ['created', 'inspection', 'pending_approval', 'approved', 'rejected', 'pending_assignment', 'deviation_hold', 'held', 'cancelled'],
        'delivered' => ['delivered', 'completed', 'returned'],
    ];

    public const LABELS = [
        'created' => 'Kiriman dibuat', 'inspection' => 'Menunggu pemeriksaan',
        'pending_approval' => 'Menunggu persetujuan', 'approved' => 'Siap ditugaskan',
        'rejected' => 'Pemeriksaan ditahan', 'pending_assignment' => 'Menunggu penugasan',
        'assigned' => 'Menunggu jawaban kurir', 'accepted' => 'Kurir siap mengambil',
        'in_transit' => 'Dalam perjalanan', 'arrived' => 'Tiba di tujuan',
        'deviation_hold' => 'Ditahan karena penyimpangan', 'held' => 'Ditahan untuk pemeriksaan',
        'delivered' => 'Sudah diterima', 'completed' => 'Selesai dan ditutup',
        'returning' => 'Dalam proses retur', 'returned' => 'Sudah dikembalikan',
        'cancelled' => 'Dibatalkan',
    ];

    public function owned(User $user): Builder
    {
        abort_unless(in_array($user->role, ['buyer', 'vendor'], true), 403, 'Halaman ini khusus vendor atau penerima.');
        abort_unless($user->is_active, 403, 'Akun tidak aktif.');
        abort_unless($user->tokenCan('vendor'), 403, 'Token tidak memiliki akses vendor.');

        return Shipment::query()->where('buyer_id', $user->id);
    }

    public function find(User $user, string $awb): Shipment
    {
        return $this->owned($user)->where('tracking_number', $awb)->firstOrFail();
    }

    public function counts(User $user): array
    {
        $statuses = $this->owned($user)->selectRaw('status, COUNT(*) AS total')->groupBy('status')->pluck('total', 'status');
        $counts = ['all' => (int) $statuses->sum()];
        foreach (self::CATEGORY_STATUSES as $category => $values) {
            $counts[$category] = (int) collect($values)->sum(fn ($status) => (int) ($statuses[$status] ?? 0));
        }

        return $counts;
    }

    public function filtered(User $user, array $filters): Builder
    {
        $query = $this->owned($user);
        if (($filters['category'] ?? 'all') !== 'all') {
            $query->whereIn('status', self::CATEGORY_STATUSES[$filters['category']]);
        }
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        $search = trim($filters['search'] ?? '');
        if ($search !== '') {
            $pattern = '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], mb_strtolower($search)).'%';
            $query->where(function (Builder $query) use ($pattern) {
                foreach (['tracking_number', 'item_summary', 'sender_name', 'recipient_name', 'origin_city', 'destination_city'] as $column) {
                    $query->orWhereRaw("LOWER($column) LIKE ? ESCAPE '!'", [$pattern]);
                }
            });
        }

        return $query;
    }

    public function category(string $status): ?string
    {
        foreach (self::CATEGORY_STATUSES as $category => $values) {
            if (in_array($status, $values, true)) {
                return $category;
            }
        }

        return null;
    }

    public function serialize(Shipment $shipment): array
    {
        $shipment->loadMissing(['latestTelemetry', 'latestInspection.checker']);
        $sample = $shipment->latestTelemetry;
        $fresh = $sample && $sample->recorded_at->between(now()->subMinutes(15), now()->addMinute());
        $temp = $sample?->temperature;
        $safe = $temp !== null && $shipment->temperature_min !== null && $shipment->temperature_max !== null
            && $temp >= $shipment->temperature_min && $temp <= $shipment->temperature_max;
        $category = $this->category($shipment->status);
        $action = $category === 'delivered' ? 'pod' : ($category === 'qc' ? 'warehouse' : 'live');
        $data = $shipment->only([
            'id', 'tracking_number', 'buyer_id', 'inspector_id', 'created_by', 'sender_name',
            'recipient_name', 'recipient_phone', 'address', 'origin_city', 'destination_city',
            'item_summary', 'package_count', 'weight_kg', 'package_type', 'batch_number',
            'handling_instructions', 'temperature_profile', 'temperature_min', 'temperature_max',
            'status', 'last_location', 'version', 'recipient_ready', 'is_demo',
        ]);

        return $data + [
            'scheduled_at' => $shipment->scheduled_at?->toISOString(),
            'completed_at' => $shipment->completed_at?->toISOString(),
            'created_at' => $shipment->created_at->toISOString(),
            'updated_at' => $shipment->updated_at->toISOString(),
            'current_temperature' => $temp, 'current_speed_kmh' => $sample?->speed_kmh,
            'telemetry_recorded_at' => $sample?->recorded_at->toISOString(),
            'telemetry_fresh' => (bool) $fresh, 'telemetry_simulated' => $sample?->is_simulated,
            'category' => $category, 'status_label' => self::LABELS[$shipment->status] ?? $shipment->status,
            'route_display' => $shipment->origin_city.' → '.$shipment->destination_city,
            'temp_label' => ! $sample ? 'Belum ada data sensor' : (! $fresh ? 'Data sensor belum diperbarui' : ($safe ? 'Dalam rentang aman' : 'Di luar rentang aman')),
            'estimate_label' => $shipment->scheduled_at ? $shipment->scheduled_at->timezone('Asia/Jakarta')->format('d M Y, H:i').' WIB' : 'Belum dijadwalkan',
            'action_type' => $action,
            'action_label' => ['pod' => 'Lihat laporan', 'warehouse' => 'Pantau pemeriksaan', 'live' => 'Lacak kiriman'][$action],
            'inspection' => $shipment->latestInspection ? $shipment->latestInspection->only([
                'id', 'shipment_id', 'packaging', 'seal', 'label', 'note', 'temperature', 'passed', 'sensor_device', 'hub_name',
            ]) + [
                'checker_name' => $shipment->latestInspection->checker?->name,
                'inspected_at' => $shipment->latestInspection->inspected_at->toISOString(),
            ] : null,
        ];
    }
}
