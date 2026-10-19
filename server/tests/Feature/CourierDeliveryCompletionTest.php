<?php

namespace Tests\Feature;

use App\Jobs\SendShipmentReport;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CourierDeliveryCompletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_delivery_proof_uses_destination_contact_and_does_not_need_manual_notes(): void
    {
        Queue::fake();
        Storage::fake('public');

        $origin = $this->customerAccount(
            'Produsen Pharma',
            'manufacturer',
            'Pengirim',
            '6281111111111',
        );
        $destination = $this->customerAccount(
            'Apotek Sehat',
            'pharmacy',
            'Sari Penerima',
            '6282222222222',
        );
        $product = Product::create([
            'sku' => 'DELIVERY-1',
            'name' => 'Produk Delivery',
            'min_temperature' => 2,
            'max_temperature' => 8,
            'is_active' => true,
        ]);
        $courier = User::factory()->create();
        $courier->forceFill(['role' => 'courier', 'is_active' => true])->save();
        $shipment = Shipment::create([
            'shipment_number' => 'AP-DELIVERY-CONTACT',
            'customer_id' => $origin->customer_id,
            'origin_customer_user_id' => $origin->id,
            'destination_customer_user_id' => $destination->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'origin_address' => 'Gudang produsen',
            'destination_address' => 'Apotek tujuan',
            'courier_id' => $courier->id,
            'status' => 'in_transit',
        ]);
        Sanctum::actingAs($courier);

        $this->post("/api/shipments/{$shipment->id}/delivery-proof", [
            'photo' => UploadedFile::fake()->image('delivery.jpg'),
        ], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJsonPath('data.status', 'delivered')
            ->assertJsonPath('whatsapp_report_queued', true);

        $this->assertDatabaseHas('delivery_proofs', [
            'shipment_id' => $shipment->id,
            'recipient_name' => 'Sari Penerima',
            'notes' => null,
        ]);
        Queue::assertPushed(
            SendShipmentReport::class,
            fn (SendShipmentReport $job) => $job->shipmentId === $shipment->id
        );
    }

    private function customerAccount(
        string $companyName,
        string $companyType,
        string $contactName,
        string $phone,
    ): User {
        $customer = Customer::create([
            'company_name' => $companyName,
            'contact_name' => $contactName,
            'company_type' => $companyType,
            'whatsapp_number' => $phone,
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
