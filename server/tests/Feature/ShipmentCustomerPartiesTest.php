<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ShipmentCustomerPartiesTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_creates_a_shipment_from_a_manufacturer_to_a_distributor(): void
    {
        $origin = $this->customerAccount('Produsen Pharma', 'manufacturer');
        $destination = $this->customerAccount('Distributor Farma', 'distributor');
        $product = Product::create([
            'sku' => 'PARTY-1',
            'name' => 'Produk Pihak Shipment',
            'min_temperature' => 2,
            'max_temperature' => 8,
            'is_active' => true,
        ]);
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin', 'is_active' => true])->save();
        Sanctum::actingAs($admin);

        $response = $this->postJson('/api/shipments', [
            'origin_customer_user_id' => $origin->id,
            'destination_customer_user_id' => $destination->id,
            'product_id' => $product->id,
            'quantity' => 5,
            'origin_address' => 'Gudang produsen',
            'destination_address' => 'Gudang distributor',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.origin_customer_user.customer.company_name', 'Produsen Pharma')
            ->assertJsonPath('data.destination_customer_user.customer.company_name', 'Distributor Farma');

        $this->assertDatabaseHas('shipments', [
            'origin_customer_user_id' => $origin->id,
            'destination_customer_user_id' => $destination->id,
            'customer_id' => $origin->customer_id,
        ]);
    }

    public function test_customer_accounts_on_both_ends_can_track_the_same_shipment(): void
    {
        $origin = $this->customerAccount('Produsen Pharma', 'manufacturer');
        $destination = $this->customerAccount('Apotek Tujuan', 'pharmacy');
        $product = Product::create([
            'sku' => 'PARTY-2',
            'name' => 'Produk Tracking',
            'min_temperature' => 2,
            'max_temperature' => 8,
            'is_active' => true,
        ]);
        $shipment = Shipment::create([
            'shipment_number' => 'AP-PARTIES-1',
            'customer_id' => $origin->customer_id,
            'origin_customer_user_id' => $origin->id,
            'destination_customer_user_id' => $destination->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'origin_address' => 'Produsen',
            'destination_address' => 'Apotek',
            'status' => 'in_transit',
        ]);

        foreach ([$origin, $destination] as $account) {
            Sanctum::actingAs($account);
            $this->getJson('/api/customer/shipments')
                ->assertOk()
                ->assertJsonPath('data.0.id', $shipment->id);
            $this->getJson('/api/customer/shipments/'.$shipment->id)
                ->assertOk()
                ->assertJsonPath('data.id', $shipment->id);
        }
    }

    private function customerAccount(string $companyName, string $companyType): User
    {
        $customer = Customer::create([
            'company_name' => $companyName,
            'contact_name' => $companyName.' Contact',
            'email' => strtolower(str_replace(' ', '-', $companyName)).'@example.test',
            'company_type' => $companyType,
            'whatsapp_number' => '6281234567890',
            'whatsapp_opt_in' => true,
            'is_active' => true,
        ]);

        $user = User::factory()->create();
        $user->forceFill([
            'role' => 'customer',
            'customer_id' => $customer->id,
            'is_active' => true,
        ])->save();

        return $user;
    }
}
