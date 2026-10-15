<?php

namespace Tests\Feature;

use App\Models\Approval;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminApprovalsTest extends TestCase
{
    use RefreshDatabase;

    public function test_failed_inspection_cannot_be_approved_and_recheck_preserves_original(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $courier = User::factory()->create(['role' => 'courier']);
        $s = Shipment::factory()->assignedTo($courier, 'pending_approval')->create();
        $s->latestInspection->update(['passed' => false, 'seal' => false]);
        Sanctum::actingAs($admin, ['admin']);
        $url = '/api/v1/admin/shipments/'.$s->tracking_number;
        $body = ['version' => 1, 'request_id' => (string) Str::uuid(), 'decision' => 'approved', 'reason' => 'Percobaan persetujuan.'];
        $this->postJson($url.'/approval', $body)->assertStatus(409);
        $body['decision'] = 'held';
        $this->postJson($url.'/approval', $body)->assertOk()->assertJsonPath('data.workflow_status', 'held');
        $this->postJson($url.'/recheck', ['version' => 2, 'request_id' => (string) Str::uuid(), 'reason' => 'Ganti kemasan lalu periksa ulang.'])->assertOk()->assertJsonPath('data.workflow_status', 'inspection');
        $this->assertDatabaseCount('inspections', 1);
        $this->assertFalse($s->latestInspection->fresh()->passed);
        $this->assertSame(2, Approval::count());
    }

    public function test_assignment_reserves_capacity_and_requires_active_courier_and_approval(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $courier = User::factory()->create(['role' => 'courier', 'courier_profile' => ['capacity_kg' => 1]]);
        $s = Shipment::factory()->assignedTo($courier, 'approved')->create(['weight_kg' => 2]);
        $s->update(['courier_id' => null]);
        Sanctum::actingAs($admin, ['admin']);
        $url = '/api/v1/admin/shipments/'.$s->tracking_number.'/assign';
        $body = ['version' => 1, 'request_id' => (string) Str::uuid(), 'courier_id' => $courier->id, 'reason' => 'Penugasan dengan kapasitas.'];
        $this->postJson($url, $body)->assertStatus(409);
        $courier->update(['courier_profile' => ['capacity_kg' => 10], 'is_active' => false]);
        $this->postJson($url, $body)->assertNotFound();
        $courier->update(['is_active' => true]);
        $this->postJson($url, $body)->assertOk()->assertJsonPath('data.workflow_status', 'assigned');
        $this->postJson($url, $body)->assertOk()->assertJsonPath('replayed', true);
        $body['request_id'] = (string) Str::uuid();
        $this->postJson($url, $body)->assertStatus(409);
    }
}
