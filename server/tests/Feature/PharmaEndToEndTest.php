<?php

namespace Tests\Feature;

use App\Models\Shipment;
use App\Models\ShipmentIssue;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PharmaEndToEndTest extends TestCase
{
    use RefreshDatabase;

    private function action(int $version, array $data = []): array
    {
        return $data + ['version' => $version, 'request_id' => (string) Str::uuid()];
    }

    public function test_admin_checker_courier_vendor_share_one_real_delivery(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create(['role' => 'admin']);
        $checker = User::factory()->create(['role' => 'checker']);
        $buyer = User::factory()->create(['role' => 'buyer']);
        $courier = User::factory()->create(['role' => 'courier', 'courier_profile' => ['capacity_kg' => 30]]);
        Sanctum::actingAs($admin, ['admin']);
        $body = ['request_id' => (string) Str::uuid(), 'tracking_number' => 'PHA-E2E-001', 'buyer_id' => $buyer->id, 'inspector_id' => $checker->id,
            'sender_name' => 'Distributor uji', 'recipient_name' => 'RS uji', 'recipient_phone' => '+628123456789',
            'address' => 'Jalan rumah sakit uji', 'origin_city' => 'Bandung', 'destination_city' => 'Jakarta',
            'item_summary' => 'Obat uji', 'package_count' => 2, 'weight_kg' => 3, 'temperature_profile' => 'controlled',
            'temperature_min' => 2, 'temperature_max' => 8, 'scheduled_at' => now()->addHour()->toISOString(),
            'handling_instructions' => 'Simpan dingin.'];
        $this->postJson('/api/v1/admin/shipments', $body)->assertCreated()->assertJsonPath('data.workflow_status', 'inspection');
        $this->postJson('/api/v1/admin/shipments', $body)->assertCreated()->assertJsonPath('replayed', true);
        $this->assertDatabaseCount('shipments', 1);
        $awb = 'PHA-E2E-001';
        $adminUrl = '/api/v1/admin/shipments/'.$awb;
        $this->postJson($adminUrl.'/assign', $this->action(1, ['courier_id' => $courier->id, 'reason' => 'Uji sebelum QC.']))->assertStatus(409);
        Sanctum::actingAs($checker, ['checker']);
        $this->getJson('/api/v1/checker/queue')->assertOk()->assertJsonPath('meta.total', 1);
        $this->post('/api/v1/checker/shipments/'.$awb.'/inspection', $this->action(1, [
            'packaging' => true, 'seal' => true, 'label' => true, 'temperature' => 4,
            'note' => 'Kemasan dan segel utuh.', 'photo' => UploadedFile::fake()->image('inspection.png'),
        ]), ['Accept' => 'application/json'])->assertOk()->assertJsonPath('data.passed', true)->assertJsonPath('data.workflow_status', 'pending_approval');
        $this->getJson('/api/v1/checker/history')->assertOk()->assertJsonPath('meta.total', 1);
        Sanctum::actingAs($admin, ['admin']);
        $this->get($adminUrl.'/inspection-photo')->assertOk();
        $this->postJson($adminUrl.'/approval', $this->action(2, ['decision' => 'approved', 'reason' => 'Bukti pemeriksaan sesuai.']))->assertOk()->assertJsonPath('data.version', 3);
        $this->postJson($adminUrl.'/assign', $this->action(3, ['courier_id' => $courier->id, 'reason' => 'Kapasitas tersedia.']))->assertOk()->assertJsonPath('data.workflow_status', 'assigned');
        Sanctum::actingAs($courier, ['courier']);
        $url = '/api/v1/courier/tasks/'.$awb;
        $this->postJson($url.'/accept', $this->action(4, ['pre_trip' => ['seal' => true, 'icepack' => true, 'manifest' => true, 'temperature' => 4]]))->assertOk();
        $this->postJson($url.'/depart', $this->action(5))->assertOk();
        $this->postJson($url.'/telemetry', ['request_id' => (string) Str::uuid(), 'temperature' => 4, 'latitude' => -6.2,
            'longitude' => 106.8, 'recorded_at' => now()->toISOString()])->assertOk()->assertJsonPath('data.is_simulated', false);
        $this->postJson($url.'/arrive', $this->action(6))->assertOk();
        $pod = $this->post($url.'/handover', $this->action(7, ['mode' => 'normal', 'recipient_name' => 'Petugas farmasi uji',
            'temperature' => 4, 'seal_intact' => true, 'latitude' => -6.2, 'longitude' => 106.8,
            'photo' => UploadedFile::fake()->image('pod.png')]), ['Accept' => 'application/json'])->assertOk()->assertJsonPath('data.workflow_status', 'completed');
        $this->assertDatabaseCount('shipment_reports', 1);
        Sanctum::actingAs($buyer, ['vendor']);
        $this->getJson('/api/v1/vendor/shipments/'.$awb)->assertOk()->assertJsonPath('data.status', 'completed')->assertJsonPath('data.available_reports_count', 1);
        $this->getJson('/api/v1/vendor/shipments/'.$awb.'/reports')->assertOk()->assertJsonPath('meta.total', 1);
        $this->getJson('/api/v1/vendor/shipments/'.$awb.'/proof')->assertOk()->assertJsonPath('data.digital_signature_verified', false);
        $this->get('/api/v1/vendor/shipments/'.$awb.'/proof/photo')->assertOk();
        $this->get('/api/v1/vendor/shipments/'.$awb.'/proof/signature')->assertNotFound();
        $pdf = $this->get('/api/v1/vendor/reports/'.$pod->json('data.report_id').'/download')->assertOk();
        $this->assertStringStartsWith('%PDF-', $pdf->getContent());
        $this->getJson('/api/v1/admin/shipments')->assertForbidden();
        Sanctum::actingAs($admin, ['admin']);
        $report = $pod->json('data.report_id');
        $this->postJson('/api/v1/admin/reports/'.$report.'/distribute')->assertAccepted()->assertJsonPath('data.status', 'queued');
        $this->postJson('/api/v1/admin/reports/'.$report.'/distribute')->assertAccepted();
        $this->getJson('/api/v1/admin/reports/daily')->assertOk()->assertJsonPath('data.regulatory_certification', false);
        $this->postJson($adminUrl.'/cancel', $this->action(8, ['reason' => 'Coba setelah selesai.']))->assertStatus(409);
    }

    public function test_return_requires_admin_decision_then_courier_acknowledgement(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create(['role' => 'admin']);
        $courier = User::factory()->create(['role' => 'courier']);
        $shipment = Shipment::factory()->assignedTo($courier, 'in_transit')->create();
        $awb = $shipment->tracking_number;
        Sanctum::actingAs($courier, ['courier']);
        $this->postJson('/api/v1/courier/tasks/'.$awb.'/issues', $this->action(1, ['category' => 'seal', 'description' => 'Segel kotak ditemukan robek.']))->assertOk();
        $issue = ShipmentIssue::firstOrFail();
        Sanctum::actingAs($admin, ['admin']);
        $decision = $this->action(2, ['action' => 'return', 'reason' => 'Segel rusak; kembalikan ke pengirim.']);
        $url = '/api/v1/admin/shipments/'.$awb.'/issues/'.$issue->id.'/decision';
        $this->postJson($url, $decision)->assertOk()->assertJsonPath('data.workflow_status', 'deviation_hold');
        $this->postJson($url, $decision)->assertOk()->assertJsonPath('replayed', true);
        Sanctum::actingAs($courier, ['courier']);
        $base = '/api/v1/courier/tasks/'.$awb;
        $this->postJson($base.'/confirm-return', $this->action(3))->assertOk();
        $this->post($base.'/handover', $this->action(4, ['mode' => 'return', 'recipient_name' => 'Petugas gudang uji',
            'temperature' => 12, 'seal_intact' => false, 'latitude' => -6.2, 'longitude' => 106.8,
            'photo' => UploadedFile::fake()->image('return.png')]), ['Accept' => 'application/json'])->assertOk()->assertJsonPath('data.workflow_status', 'returned');
    }
}
