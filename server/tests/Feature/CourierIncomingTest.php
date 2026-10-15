<?php

namespace Tests\Feature;

use App\Models\Shipment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CourierIncomingTest extends TestCase
{
    use RefreshDatabase;

    private function payload(): array
    {
        return ['version' => 1, 'request_id' => (string) Str::uuid(),
            'pre_trip' => ['seal' => true, 'icepack' => true, 'manifest' => true, 'temperature' => 4]];
    }

    public function test_accept_requires_approval_and_checklist_and_is_not_departure(): void
    {
        $courier = User::factory()->create(['role' => 'courier', 'courier_profile' => ['capacity_kg' => 10]]);
        $shipment = Shipment::factory()->assignedTo($courier)->create();
        Sanctum::actingAs($courier, ['courier']);
        $url = '/api/v1/courier/tasks/'.$shipment->tracking_number.'/accept';
        $payload = $this->payload();
        $this->postJson($url, $payload)->assertOk()->assertJsonPath('data.workflow_status', 'accepted');
        $this->postJson($url, $payload)->assertOk()->assertJsonPath('replayed', true);
        $this->assertDatabaseCount('events', 1);
        $this->assertSame('accepted', $shipment->fresh()->status);
    }

    public function test_unsafe_unapproved_over_capacity_and_foreign_tasks_are_blocked(): void
    {
        $courier = User::factory()->create(['role' => 'courier', 'courier_profile' => ['capacity_kg' => 0.5]]);
        $shipment = Shipment::factory()->assignedTo($courier)->create();
        Sanctum::actingAs($courier, ['courier']);
        $url = '/api/v1/courier/tasks/'.$shipment->tracking_number.'/accept';
        $this->postJson($url, $this->payload())->assertStatus(409);
        $payload = $this->payload();
        $payload['pre_trip']['seal'] = false;
        $this->postJson($url, $payload)->assertUnprocessable();
        $shipment->latestApproval->update(['decision' => 'held']);
        $this->postJson($url, $this->payload())->assertStatus(409);
        $foreign = Shipment::factory()->create();
        $this->getJson('/api/v1/courier/tasks/'.$foreign->tracking_number)->assertNotFound();
        $this->assertDatabaseCount('events', 0);
    }

    public function test_reject_returns_to_admin_without_auto_assignment_and_retry_works(): void
    {
        $courier = User::factory()->create(['role' => 'courier']);
        $shipment = Shipment::factory()->assignedTo($courier)->create();
        Sanctum::actingAs($courier, ['courier']);
        $url = '/api/v1/courier/tasks/'.$shipment->tracking_number.'/reject';
        $payload = ['version' => 1, 'request_id' => (string) Str::uuid(), 'category' => 'equipment', 'notes' => 'Cold-box tidak siap.'];
        $this->postJson($url, $payload)->assertOk();
        $this->postJson($url, $payload)->assertOk()->assertJsonPath('replayed', true);
        $this->assertNull($shipment->fresh()->courier_id);
        $this->assertSame('approved', $shipment->fresh()->status);
        $this->assertDatabaseCount('assignments', 1);
    }

    public function test_stale_version_and_changed_retry_payload_are_rejected(): void
    {
        $courier = User::factory()->create(['role' => 'courier', 'courier_profile' => ['capacity_kg' => 10]]);
        $shipment = Shipment::factory()->assignedTo($courier)->create();
        Sanctum::actingAs($courier, ['courier']);
        $url = '/api/v1/courier/tasks/'.$shipment->tracking_number.'/accept';
        $payload = $this->payload();
        $payload['version'] = 99;
        $this->postJson($url, $payload)->assertStatus(409);
        $payload['version'] = 1;
        $this->postJson($url, $payload)->assertOk();
        $payload['pre_trip']['temperature'] = 5;
        $this->postJson($url, $payload)->assertStatus(409);
    }
}
