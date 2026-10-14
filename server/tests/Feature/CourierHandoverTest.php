<?php

namespace Tests\Feature;

use App\Models\Shipment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CourierHandoverTest extends TestCase
{
    use RefreshDatabase;

    private function payload(): array
    {
        return ['version' => 1, 'request_id' => (string) Str::uuid(), 'mode' => 'normal',
            'recipient_name' => 'Petugas Farmasi Demo', 'temperature' => 4, 'seal_intact' => true,
            'latitude' => -6.2, 'longitude' => 106.8, 'photo' => UploadedFile::fake()->image('pod.png')];
    }

    public function test_pod_closes_once_produces_real_pdf_and_private_photo(): void
    {
        Storage::fake('local');
        $courier = User::factory()->create(['role' => 'courier']);
        $shipment = Shipment::factory()->assignedTo($courier, 'arrived')->create();
        Sanctum::actingAs($courier, ['courier']);
        $url = '/api/v1/courier/tasks/'.$shipment->tracking_number;
        $payload = $this->payload();
        $response = $this->post($url.'/handover', $payload, ['Accept' => 'application/json'])->assertOk()->assertJsonPath('data.workflow_status', 'completed');
        $this->post($url.'/handover', $payload, ['Accept' => 'application/json'])->assertOk()->assertJsonPath('replayed', true);
        $this->assertDatabaseCount('delivery_proofs', 1);
        $this->assertDatabaseCount('shipment_reports', 1);
        $this->get($url.'/proof/photo')->assertOk()->assertHeader('Content-Type', 'image/png');
        $pdf = $this->get('/api/v1/courier/reports/'.$response->json('data.report_id').'/download')->assertOk();
        $this->assertStringStartsWith('%PDF-', $pdf->getContent());
        $this->postJson($url.'/issues', ['version' => 2, 'request_id' => (string) Str::uuid(), 'category' => 'seal', 'description' => 'Laporan setelah selesai.'])->assertStatus(409);
        Sanctum::actingAs(User::factory()->create(['role' => 'courier']), ['courier']);
        $this->get('/api/v1/courier/reports/'.$response->json('data.report_id').'/download')->assertNotFound();
        $this->getJson($url.'/proof/photo')->assertNotFound();
    }

    public function test_unsafe_no_photo_no_arrival_and_return_without_admin_are_blocked(): void
    {
        Storage::fake('local');
        $courier = User::factory()->create(['role' => 'courier']);
        $shipment = Shipment::factory()->assignedTo($courier, 'in_transit')->create();
        Sanctum::actingAs($courier, ['courier']);
        $url = '/api/v1/courier/tasks/'.$shipment->tracking_number.'/handover';
        $payload = $this->payload();
        $this->post($url, $payload, ['Accept' => 'application/json'])->assertStatus(409);
        $shipment->update(['status' => 'arrived']);
        $payload['temperature'] = 12;
        $this->post($url, $payload, ['Accept' => 'application/json'])->assertUnprocessable();
        $payload['temperature'] = 4;
        unset($payload['photo']);
        $this->post($url, $payload, ['Accept' => 'application/json'])->assertUnprocessable();
        $shipment->update(['status' => 'returning']);
        $payload = $this->payload();
        $payload['mode'] = 'return';
        $this->post($url, $payload, ['Accept' => 'application/json'])->assertStatus(409);
        $this->assertDatabaseCount('delivery_proofs', 0);
        $this->assertCount(0, Storage::disk('local')->allFiles());
    }
}
