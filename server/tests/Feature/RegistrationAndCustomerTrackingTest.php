<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Shipment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use App\Models\User;

class RegistrationAndCustomerTrackingTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_registration_creates_a_customer_account(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Sari Customer',
            'email' => 'sari@example.test',
            'password' => 'secure-password-123',
            'password_confirmation' => 'secure-password-123',
            'company_name' => 'Apotek Sehat',
            'company_type' => 'pharmacy',
            'whatsapp_number' => '6281234567890',
            'whatsapp_opt_in' => true,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.user.role', 'customer')
            ->assertJsonPath('data.user.name', 'Sari Customer')
            ->assertJsonStructure(['data' => ['token', 'user' => ['customer_id']]]);

        $this->assertDatabaseHas('customers', [
            'company_name' => 'Apotek Sehat',
            'whatsapp_opt_in' => true,
        ]);
        $this->assertDatabaseHas('users', [
            'email' => 'sari@example.test',
            'role' => 'customer',
        ]);
    }

    public function test_customer_can_only_see_shipments_for_their_company(): void
    {
        $customer = Customer::create([
            'company_name' => 'Mitra Utama',
            'company_type' => 'distributor',
        ]);
        $otherCustomer = Customer::create([
            'company_name' => 'Mitra Lain',
            'company_type' => 'pharmacy',
        ]);
        $user = User::factory()->create();
        $user->forceFill([
            'role' => 'customer',
            'customer_id' => $customer->id,
            'is_active' => true,
        ])->save();
        $product = Product::create([
            'sku' => 'TRACK-1',
            'name' => 'Obat Tracking',
            'min_temperature' => 2,
            'max_temperature' => 8,
            'is_active' => true,
        ]);
        $shipment = Shipment::create([
            'shipment_number' => 'AP-CUSTOMER-OWN',
            'customer_id' => $customer->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'origin_address' => 'Gudang',
            'destination_address' => 'Apotek',
            'status' => 'in_transit',
        ]);
        Shipment::create([
            'shipment_number' => 'AP-CUSTOMER-OTHER',
            'customer_id' => $otherCustomer->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'origin_address' => 'Gudang lain',
            'destination_address' => 'Apotek lain',
            'status' => 'in_transit',
        ]);
        Sanctum::actingAs($user);

        $this->getJson('/api/customer/shipments')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $shipment->id);
        $this->getJson('/api/customer/shipments/'.$shipment->id)
            ->assertOk()
            ->assertJsonPath('data.shipment_number', 'AP-CUSTOMER-OWN');
        $this->getJson('/api/customer/shipments/99999')
            ->assertNotFound();
    }
}
