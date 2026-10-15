<?php

namespace Tests\Feature;

use App\Models\Shipment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminMonitorTest extends TestCase
{
    use RefreshDatabase;

    public function test_temperature_prediction_uses_recorded_samples_not_hardcoded_banner(): void
    {
        $this->freezeTime();
        $admin = User::factory()->create(['role' => 'admin']);
        $s = Shipment::factory()->create(['status' => 'in_transit']);
        Sanctum::actingAs($admin, ['admin']);
        $url = '/api/v1/admin/shipments/'.$s->tracking_number.'/prediction';
        $this->getJson($url)->assertOk()->assertJsonPath('data.available', false);
        foreach ([[4, 6], [2, 7], [0, 7.9]] as [$minutes, $temperature]) {
            $s->telemetry()->create(['temperature' => $temperature, 'recorded_at' => now()->subMinutes($minutes), 'is_simulated' => true]);
        }
        $this->getJson($url)->assertOk()->assertJsonPath('data.available', true)->assertJsonPath('data.warning', true)->assertJsonPath('data.is_simulated', true);
        $this->travel(3)->minutes();
        $this->getJson($url)->assertOk()->assertJsonPath('data.available', false);
    }
}
