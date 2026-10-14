<?php

namespace Tests\Feature;

use App\Models\Decision;
use App\Models\DeliveryProof;
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

    public function test_return_accepts_admin_decision_and_keeps_proof_immutable(): void
    {
        Storage::fake('local');
        $courier = User::factory()->create(['role' => 'courier']);
        $shipment = Shipment::factory()->assignedTo($courier, 'returning')->create();
        $issue = $shipment->issues()->create(['type' => 'seal', 'description' => 'Segel rusak.', 'status' => 'resolved']);
        Decision::create(['issue_id' => $issue->id, 'user_id' => User::factory()->create(['role' => 'admin'])->id,
            'action' => 'return', 'reason' => 'Kembalikan ke gudang pengirim.']);
        Sanctum::actingAs($courier, ['courier']);
        $payload = $this->payload();
        $payload['mode'] = 'return';
        $payload['temperature'] = 12;
        $payload['seal_intact'] = false;
        $payload['signature'] = UploadedFile::fake()->image('signature.png');
        $url = '/api/v1/courier/tasks/'.$shipment->tracking_number;
        $this->post($url.'/handover', $payload, ['Accept' => 'application/json'])->assertOk()->assertJsonPath('data.workflow_status', 'returned');
        $this->get($url.'/proof/signature')->assertOk()->assertHeader('Content-Type', 'image/png');
        $this->getJson($url.'/handover')->assertOk()->assertJsonPath('data.can_submit', false);
        $this->assertCount(2, Storage::disk('local')->allFiles());
        $this->expectException(\LogicException::class);
        DeliveryProof::firstOrFail()->update(['recipient_name' => 'Edited']);
    }
}
