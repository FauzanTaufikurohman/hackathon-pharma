<?php

namespace Tests\Feature;

use App\Jobs\SendShipmentReport;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ShipmentCancellationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_cancellation_queues_final_report_for_opted_in_customer(): void
    {
        Queue::fake();

        $customer = Customer::create([
            'company_name' => 'Distributor Test',
            'company_type' => 'distributor',
            'whatsapp_number' => '6281234567890',
            'whatsapp_opt_in' => true,
        ]);
        $product = Product::create([
            'sku' => 'TEST-1',
            'name' => 'Obat test',
            'min_temperature' => 2,
            'max_temperature' => 8,
            'is_active' => true,
        ]);
        $shipment = Shipment::create([
            'shipment_number' => 'AP-CANCEL-TEST',
            'customer_id' => $customer->id,
            'product_id' => $product->id,
            'quantity' => 4,
            'origin_address' => 'Gudang A',
            'destination_address' => 'Apotek B',
            'status' => 'in_transit',
        ]);
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);
        Sanctum::actingAs($admin);

        $this->postJson("/api/shipments/{$shipment->id}/cancel", [
            'reason' => 'Kerusakan kemasan terdeteksi',
        ])->assertOk()
            ->assertJsonPath('data.status', 'cancelled');

        $this->assertDatabaseHas('shipment_events', [
            'shipment_id' => $shipment->id,
            'event_type' => 'shipment_cancelled',
            'from_status' => 'in_transit',
            'to_status' => 'cancelled',
        ]);
        $this->assertNotNull($shipment->fresh()->cancelled_at);

        Queue::assertPushed(
            SendShipmentReport::class,
            fn (SendShipmentReport $job) => $job->shipmentId === $shipment->id
        );
    }
}
