<?php

namespace App;

use App\Models\Shipment;
use App\Models\ShipmentIssue;
use App\Models\TelemetrySample;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class CourierTelemetryService
{
    public function append(Shipment $shipment, array $data): array
    {
        abort_unless(in_array($shipment->status, ['in_transit', 'arrived', 'returning', 'held', 'deviation_hold'], true)
            && $shipment->latestAssignment?->status === 'accepted', 409, 'Telemetry hanya untuk tugas yang sedang ditangani kurir.');
        $sample = TelemetrySample::create([
            'shipment_id' => $shipment->id, 'temperature' => (float) $data['temperature'],
            'latitude' => (float) $data['latitude'], 'longitude' => (float) $data['longitude'],
            'speed_kmh' => $data['speed_kmh'] ?? null, 'sensor_id' => $data['sensor_id'] ?? null,
            'battery_pct' => $data['battery_pct'] ?? null, 'is_simulated' => $shipment->is_demo,
            'recorded_at' => CarbonImmutable::parse($data['recorded_at'])->utc(),
        ]);
        $this->evaluateTemperature($shipment);

        return ['sample_id' => $sample->id, 'workflow_status' => $shipment->status, 'version' => $shipment->version,
            'recorded_at' => $sample->recorded_at->toISOString(), 'is_simulated' => $sample->is_simulated];
    }

    public function hold(Shipment $shipment, string $type, string $description, ?TelemetrySample $sample = null): void
    {
        if ($shipment->issues()->where('status', 'open')->where('type', $type)->exists()) {
            return;
        }
        $from = $shipment->status;
        ShipmentIssue::create([
            'shipment_id' => $shipment->id, 'type' => $type, 'description' => $description,
            'previous_status' => $from, 'temperature' => $sample?->temperature,
            'latitude' => $sample?->latitude, 'longitude' => $sample?->longitude, 'status' => 'open',
        ]);
        $shipment->status = 'deviation_hold';
        $shipment->version++;
        $shipment->save();
        $workflow = app(CourierWorkflow::class);
        $event = $workflow->record($shipment, null, 'anomaly_hold', 'Kiriman ditahan untuk keputusan Admin', $from,
            ['incident_type' => $type, 'temperature' => $sample?->temperature], $description);
        $workflow->notify($shipment, $event);
    }

    private function evaluateTemperature(Shipment $shipment): void
    {
        if ($shipment->temperature_min === null || $shipment->temperature_max === null) {
            return;
        }
        $samples = $shipment->telemetry()->where('recorded_at', '>=', now()->subMinutes(20))
            ->orderByDesc('recorded_at')->orderByDesc('id')->limit(1000)->get();
        $latest = $samples->first();
        if (! $latest || $latest->recorded_at->lt(now()->subMinutes(2))) {
            return;
        }
        $lastTime = $latest->recorded_at;
        $oldestUnsafe = $lastTime;
        foreach ($samples as $sample) {
            if ($sample->temperature === null || ($sample->temperature >= $shipment->temperature_min && $sample->temperature <= $shipment->temperature_max)
                || abs($lastTime->getTimestamp() - $sample->recorded_at->getTimestamp()) > 120) {
                break;
            }
            $oldestUnsafe = $sample->recorded_at;
            $lastTime = $sample->recorded_at;
        }
        if ($latest->recorded_at->getTimestamp() - $oldestUnsafe->getTimestamp() >= 180) {
            $this->hold($shipment, 'temperature', 'Suhu berada di luar rentang kiriman selama sekurang-kurangnya 3 menit berturut-turut.', $latest);
        }
    }

    public function checkSignalLoss(): int
    {
        $count = 0;
        Shipment::whereIn('status', ['in_transit', 'arrived', 'returning'])->orderBy('id')->chunkById(100, function ($shipments) use (&$count) {
            foreach ($shipments as $item) {
                DB::transaction(function () use ($item, &$count) {
                    $shipment = Shipment::whereKey($item->id)->lockForUpdate()->firstOrFail();
                    if (! in_array($shipment->status, ['in_transit', 'arrived', 'returning'], true)) {
                        return;
                    }
                    $latest = $shipment->latestTelemetry;
                    $baseline = $latest?->recorded_at ?? $shipment->events()->where('type', 'depart')->latest('id')->first()?->created_at ?? $shipment->updated_at;
                    if ($baseline->lt(now()->subMinutes(15))) {
                        $this->hold($shipment, 'signal_loss', 'Telemetry tidak diperbarui selama lebih dari 15 menit; Admin perlu memeriksa.', $latest);
                        $count++;
                    }
                }, 3);
            }
        });

        return $count;
    }
}
