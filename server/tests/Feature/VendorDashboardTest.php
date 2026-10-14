<?php

namespace Tests\Feature;

use App\Models\Shipment;
use App\Models\ShipmentEvent;
use App\Models\ShipmentIssue;
use App\Models\TelemetrySample;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class VendorDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_metrics_use_owned_fresh_data_and_real_open_issues(): void
    {
        $vendor = User::factory()->create();
        $shipment = Shipment::factory()->create(['buyer_id' => $vendor->id, 'status' => 'in_transit']);
        $held = Shipment::factory()->create(['buyer_id' => $vendor->id, 'status' => 'deviation_hold']);
        Shipment::factory()->create(['buyer_id' => $vendor->id, 'status' => 'completed']);
        Shipment::factory()->count(3)->create();
        TelemetrySample::create(['shipment_id' => $shipment->id, 'temperature' => 4, 'recorded_at' => now()]);
        TelemetrySample::create(['shipment_id' => $held->id, 'temperature' => 12, 'recorded_at' => now()]);
        ShipmentIssue::create(['shipment_id' => $held->id, 'type' => 'temperature', 'description' => 'Tinggi']);
        Sanctum::actingAs($vendor);
        $this->getJson('/api/v1/vendor/dashboard')->assertOk()
            ->assertJsonPath('data.metrics.active_shipments_count', 2)
            ->assertJsonPath('data.metrics.critical_count', 1)
            ->assertJsonPath('data.metrics.incident_count', 1)
            ->assertJsonPath('data.metrics.compliance_rate', 50)
            ->assertJsonPath('data.priority_shipment.id', $held->id);
    }

    public function test_empty_or_stale_telemetry_does_not_claim_compliance(): void
    {
        $vendor = User::factory()->create();
        Sanctum::actingAs($vendor);
        $this->getJson('/api/v1/vendor/dashboard')->assertOk()
            ->assertJsonPath('data.priority_shipment', null)
            ->assertJsonPath('data.metrics.compliance_rate', null);
        $shipment = Shipment::factory()->create(['buyer_id' => $vendor->id]);
        TelemetrySample::create(['shipment_id' => $shipment->id, 'temperature' => 4, 'recorded_at' => now()->subHour()]);
        $this->getJson('/api/v1/vendor/dashboard')->assertOk()
            ->assertJsonPath('data.metrics.measured_shipments_count', 0)
            ->assertJsonPath('data.metrics.compliance_rate', null);
    }

    public function test_readiness_audits_queues_and_replays_once_without_changing_workflow_status(): void
    {
        $vendor = User::factory()->create();
        $courier = User::factory()->create(['role' => 'courier']);
        User::factory()->create(['role' => 'admin']);
        $shipment = Shipment::factory()->create(['buyer_id' => $vendor->id, 'courier_id' => $courier->id, 'status' => 'in_transit']);
        Sanctum::actingAs($vendor);
        $payload = ['recipient_ready' => true, 'version' => 1, 'request_id' => (string) Str::uuid()];
        $this->putJson('/api/v1/vendor/shipments/'.$shipment->tracking_number.'/readiness', $payload)
            ->assertOk()->assertJsonPath('data.version', 2)->assertJsonPath('data.status', 'in_transit');
        $this->putJson('/api/v1/vendor/shipments/'.$shipment->tracking_number.'/readiness', $payload)
            ->assertOk()->assertJsonPath('replayed', true);
        $this->assertDatabaseCount('events', 1);
        $this->assertDatabaseCount('messages', 2);
        $this->assertTrue($shipment->fresh()->recipient_ready);
    }

    public function test_wrong_owner_missing_resi_stale_version_closed_and_invalid_input(): void
    {
        $vendor = User::factory()->create();
        $own = Shipment::factory()->create(['buyer_id' => $vendor->id]);
        $other = Shipment::factory()->create();
        Sanctum::actingAs($vendor);
        $payload = ['recipient_ready' => true, 'version' => 9, 'request_id' => (string) Str::uuid()];
        $this->getJson('/api/v1/vendor/dashboard?awb='.$other->tracking_number)->assertNotFound();
        $this->putJson('/api/v1/vendor/shipments/'.$other->tracking_number.'/readiness', $payload)->assertNotFound();
        $this->putJson('/api/v1/vendor/shipments/'.$own->tracking_number.'/readiness', $payload)->assertStatus(409);
        $this->putJson('/api/v1/vendor/shipments/'.$own->tracking_number.'/readiness', [])->assertUnprocessable();
        $own->update(['status' => 'completed']);
        $payload['version'] = 1;
        $this->putJson('/api/v1/vendor/shipments/'.$own->tracking_number.'/readiness', $payload)->assertStatus(409);
        $this->assertDatabaseCount('events', 0);
    }

    public function test_request_id_cannot_be_reused_with_different_payload(): void
    {
        $vendor = User::factory()->create();
        $own = Shipment::factory()->create(['buyer_id' => $vendor->id]);
        Sanctum::actingAs($vendor);
        $payload = ['recipient_ready' => true, 'version' => 1, 'request_id' => (string) Str::uuid()];
        $url = '/api/v1/vendor/shipments/'.$own->tracking_number.'/readiness';
        $this->putJson($url, $payload)->assertOk();
        $payload['recipient_ready'] = false;
        $this->putJson($url, $payload)->assertStatus(409);
        $this->assertDatabaseCount('events', 1);
    }

    public function test_audit_event_cannot_be_edited(): void
    {
        $shipment = Shipment::factory()->create();
        $event = ShipmentEvent::create(['shipment_id' => $shipment->id, 'type' => 'test', 'title' => 'Original', 'to_status' => 'inspection', 'created_at' => now()]);
        $this->expectException(\LogicException::class);
        $event->update(['title' => 'Tampered']);
    }
}
