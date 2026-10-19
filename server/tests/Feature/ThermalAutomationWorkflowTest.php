<?php

namespace Tests\Feature;

use App\Jobs\AnalyzeThermalRecord;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ThermalAutomationWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_checker_thermal_input_queues_ai_assessment(): void
    {
        Queue::fake();

        $customer = Customer::create([
            'company_name' => 'Apotek Test',
            'company_type' => 'pharmacy',
        ]);
        $product = Product::create([
            'sku' => 'TEST-COLD',
            'name' => 'Produk pendingin',
            'min_temperature' => 2,
            'max_temperature' => 8,
            'is_active' => true,
        ]);
        $checker = User::factory()->create([
            'role' => 'checker',
            'is_active' => true,
        ]);
        $shipment = Shipment::create([
            'shipment_number' => 'AP-THERMAL-TEST',
            'customer_id' => $customer->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'origin_address' => 'Gudang A',
            'destination_address' => 'Apotek B',
            'checker_id' => $checker->id,
            'status' => 'checker_assigned',
        ]);

        Sanctum::actingAs($checker);

        $response = $this->postJson(
            "/api/shipments/{$shipment->id}/thermal-checks",
            [
                'temperature_celsius' => 9,
                'packaging_condition' => 'good',
                'physical_condition' => 'good',
            ]
        );

        $response->assertCreated()
            ->assertJsonPath('data.temperature_status', 'outside_range')
            ->assertJsonPath('data.ai_assessment_status', 'pending');

        Queue::assertPushed(
            AnalyzeThermalRecord::class,
            fn (AnalyzeThermalRecord $job) => $job->recordType === 'thermal_check'
                && $job->recordId === $response->json('data.thermal_check.id')
        );

        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);
        Sanctum::actingAs($admin);

        $this->getJson("/api/shipments/{$shipment->id}")
            ->assertOk()
            ->assertJsonPath('data.thermal_checks.0.ai_assessment_status', 'pending');

        $shipment->update(['status' => 'in_transit']);
        $incidentResponse = $this->postJson("/api/shipments/{$shipment->id}/thermal-simulations", [
            'temperature_celsius' => 12,
            'exposure_duration_seconds' => 86400,
        ])->assertCreated();

        config(['services.openai.thermal_debug' => true]);
        $this->getJson('/api/thermal-incidents/'.$incidentResponse->json('data.id').'/debug')
            ->assertOk()
            ->assertJsonPath('data.debug_enabled', true)
            ->assertJsonPath('data.ai_assessment_status', 'pending')
            ->assertJsonPath('data.diagnostics', null);

        $customerUser = User::factory()->create([
            'role' => 'customer',
            'is_active' => true,
        ]);
        Sanctum::actingAs($customerUser);
        $this->getJson('/api/thermal-incidents/'.$incidentResponse->json('data.id').'/debug')
            ->assertForbidden();
        Sanctum::actingAs($admin);

        $shipment->update(['status' => 'delivered']);
        $this->postJson("/api/shipments/{$shipment->id}/thermal-simulations", [
            'temperature_celsius' => 12,
            'exposure_duration_seconds' => 86400,
        ])->assertUnprocessable();
        $this->getJson('/api/thermal-incidents')
            ->assertOk()
            ->assertJsonCount(0, 'data');
        $this->patchJson('/api/thermal-incidents/'.$incidentResponse->json('data.id').'/resolve', [
            'corrective_action' => 'Suhu telah dinormalkan.',
            'assessment_note' => 'Pemeriksaan telah dilakukan.',
        ])->assertUnprocessable();

        Queue::assertPushed(
            AnalyzeThermalRecord::class,
            fn (AnalyzeThermalRecord $job) => $job->recordType === 'thermal_incident'
        );
    }
}
