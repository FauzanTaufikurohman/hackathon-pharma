<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

class ShipmentWorkflowTest extends TestCase
{
    public function test_shipment_list_requires_authentication(): void
    {
        $response = $this->getJson('/api/shipments');

        $response->assertUnauthorized();
    }

    public function test_thermal_incident_list_requires_authentication(): void
    {
        $response = $this->getJson('/api/thermal-incidents');

        $response->assertUnauthorized();
    }
    use RefreshDatabase;
    public function test_courier_cannot_access_admin_customer_endpoint(): void
    {
        $courier = User::factory()->create();

        $courier->forceFill([
            'role' => 'courier',
            'is_active' => true,
        ])->save();

        Sanctum::actingAs($courier);

        $this->getJson('/api/customers')
            ->assertForbidden();
    }
}