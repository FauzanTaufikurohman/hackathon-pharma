<?php

namespace Tests\Feature;

use App\Models\Shipment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class VendorTelemetryModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_telemetry_tab_only_reads_own_recorded_samples_and_marks_stale_data(): void
    {
        $vendor = User::factory()->create(['role' => 'buyer']);
        $shipment = Shipment::factory()->create(['buyer_id' => $vendor->id]);
        $foreign = Shipment::factory()->create();
        $shipment->telemetry()->create(['temperature' => 4, 'latitude' => -6.2, 'longitude' => 106.8,
            'recorded_at' => now()->subHour(), 'is_simulated' => true]);
        Sanctum::actingAs($vendor, ['vendor']);
        $this->getJson('/api/v1/vendor/shipments/'.$shipment->tracking_number.'/telemetry')->assertOk()
            ->assertJsonPath('meta.total', 1)->assertJsonPath('meta.is_stale', true)->assertJsonPath('data.0.is_simulated', true);
        $this->getJson('/api/v1/vendor/shipments/'.$foreign->tracking_number.'/telemetry')->assertNotFound();
        $this->getJson('/api/v1/vendor/shipments/'.$shipment->tracking_number.'/telemetry?per_page=9999')->assertUnprocessable();
        $this->getJson('/api/v1/vendor/shipments/MISSING-RESI/telemetry')->assertNotFound();
    }
}
