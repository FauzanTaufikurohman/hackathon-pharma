<?php

namespace Tests\Feature;

use App\CourierTelemetryService;
use App\Models\Shipment;
use App\Models\TelemetrySample;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CourierDeliveryTest extends TestCase
{
    use RefreshDatabase;

    private function action(int $version): array
    {
        return ['version' => $version, 'request_id' => (string) Str::uuid()];
    }

    public function test_depart_and_arrive_follow_order_and_replay(): void
    {
        $courier = User::factory()->create(['role' => 'courier']);
        $shipment = Shipment::factory()->assignedTo($courier, 'accepted')->create();
        Sanctum::actingAs($courier, ['courier']);
        $base = '/api/v1/courier/tasks/'.$shipment->tracking_number;
        $this->postJson($base.'/arrive', $this->action(1))->assertStatus(409);
        $payload = $this->action(1);
        $this->postJson($base.'/depart', $payload)->assertOk()->assertJsonPath('data.workflow_status', 'in_transit');
        $this->postJson($base.'/depart', $payload)->assertOk()->assertJsonPath('replayed', true);
        $this->postJson($base.'/arrive', $this->action(2))->assertOk()->assertJsonPath('data.workflow_status', 'arrived');
        $this->assertDatabaseCount('events', 2);
    }

    public function test_telemetry_validates_coordinates_replays_and_temperature_hold_never_auto_resumes(): void
    {
        $courier = User::factory()->create(['role' => 'courier']);
        $shipment = Shipment::factory()->assignedTo($courier, 'in_transit')->create();
        Sanctum::actingAs($courier, ['courier']);
        $url = '/api/v1/courier/tasks/'.$shipment->tracking_number.'/telemetry';
        $this->freezeTime();
        for ($i = 3; $i >= 0; $i--) {
            $payload = ['request_id' => (string) Str::uuid(), 'temperature' => 12, 'latitude' => -6.2, 'longitude' => 106.8,
                'recorded_at' => now()->subMinutes($i)->toISOString()];
            $this->postJson($url, $payload)->assertOk();
        }
        $this->postJson($url, $payload)->assertOk()->assertJsonPath('replayed', true);
        $this->assertSame('deviation_hold', $shipment->fresh()->status);
        $this->assertDatabaseCount('issues', 1);
        $this->assertDatabaseCount('telemetry_samples', 4);
        $payload['request_id'] = (string) Str::uuid();
        $payload['temperature'] = 4;
        $this->postJson($url, $payload)->assertOk();
        $this->assertSame('deviation_hold', $shipment->fresh()->status);
        $payload['latitude'] = 200;
        $this->postJson($url, $payload)->assertUnprocessable();
    }

    public function test_signal_loss_creates_single_hold_without_llm(): void
    {
        $courier = User::factory()->create(['role' => 'courier']);
        $shipment = Shipment::factory()->assignedTo($courier, 'in_transit')->create(['updated_at' => now()->subMinutes(20)]);
        TelemetrySample::create(['shipment_id' => $shipment->id, 'temperature' => 4, 'recorded_at' => now()->subMinutes(20)]);
        $engine = app(CourierTelemetryService::class);
        $this->assertSame(1, $engine->checkSignalLoss());
        $this->assertSame(0, $engine->checkSignalLoss());
        $this->assertSame('deviation_hold', $shipment->fresh()->status);
        $this->assertDatabaseCount('issues', 1);
    }
}
