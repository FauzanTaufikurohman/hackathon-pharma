<?php

namespace Tests\Feature;

use App\Models\Shipment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminShipmentsTest extends TestCase
{
    use RefreshDatabase;

    public function test_roles_invalid_ids_and_unknown_resi_do_not_leak_admin_data(): void
    {
        $this->getJson('/api/v1/admin/shipments')->assertUnauthorized();
        foreach (['buyer', 'checker', 'courier'] as $role) {
            Sanctum::actingAs(User::factory()->create(['role' => $role]), [$role]);
            $this->getJson('/api/v1/admin/shipments')->assertForbidden();
        }
        Sanctum::actingAs(User::factory()->create(['role' => 'admin']), ['admin']);
        $this->getJson('/api/v1/admin/shipments/NO-SUCH-AWB')->assertNotFound();
        $this->getJson('/api/v1/admin/metrics')->assertOk()->assertJsonPath('data.total', 0);
        $this->getJson('/api/v1/admin/shipments?per_page=9999')->assertUnprocessable();
    }

    public function test_csv_escapes_spreadsheet_formulas(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $courier = User::factory()->create(['role' => 'courier']);
        $s = Shipment::factory()->assignedTo($courier, 'in_transit')->create(['sender_name' => '=HYPERLINK("bad")', 'is_demo' => false]);
        Sanctum::actingAs($admin, ['admin']);
        $response = $this->get('/api/v1/admin/shipments/export')->assertOk();
        $this->assertStringContainsString("'=HYPERLINK", $response->streamedContent());
    }

    public function test_cancelled_assignment_is_removed_from_courier_and_vendor_active_workflow(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $courier = User::factory()->create(['role' => 'courier']);
        $buyer = User::factory()->create(['role' => 'buyer']);
        $s = Shipment::factory()->assignedTo($courier)->create(['buyer_id' => $buyer->id]);
        Sanctum::actingAs($admin, ['admin']);
        $this->postJson('/api/v1/admin/shipments/'.$s->tracking_number.'/cancel', [
            'version' => 1, 'request_id' => (string) Str::uuid(), 'reason' => 'Dibatalkan sebelum diterima kurir.',
        ])->assertOk()->assertJsonPath('data.workflow_status', 'cancelled');
        Sanctum::actingAs($courier, ['courier']);
        $this->getJson('/api/v1/courier/tasks/'.$s->tracking_number)->assertNotFound();
        Sanctum::actingAs($buyer, ['vendor']);
        $this->getJson('/api/v1/vendor/dashboard')->assertOk()->assertJsonPath('data.metrics.active_shipments_count', 0);
        $this->putJson('/api/v1/vendor/shipments/'.$s->tracking_number.'/readiness', [
            'version' => 2, 'request_id' => (string) Str::uuid(), 'recipient_ready' => true,
        ])->assertStatus(409);
    }
}
