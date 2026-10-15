<?php

namespace Tests\Feature;

use App\Models\Inspection;
use App\Models\Shipment;
use App\Models\ShipmentReport;
use App\Models\TelemetrySample;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class VendorMonitorTest extends TestCase
{
    use RefreshDatabase;

    public function test_detail_missing_and_foreign_resi_never_fall_back(): void
    {
        $vendor = User::factory()->create();
        $own = Shipment::factory()->create(['buyer_id' => $vendor->id, 'tracking_number' => '#100098421088712']);
        $other = Shipment::factory()->create();
        Sanctum::actingAs($vendor, ['vendor']);
        $response = $this->getJson('/api/v1/vendor/shipments/'.rawurlencode($own->tracking_number));
        $response->assertOk()->assertJsonPath('data.id', $own->id);
        $this->assertStringContainsString('%23', $response->json('data.links.telemetry'));
        $this->getJson('/api/v1/vendor/shipments/missing')->assertNotFound();
        foreach (['', '/telemetry', '/events', '/issues', '/reports', '/inspection-photo'] as $suffix) {
            $this->getJson('/api/v1/vendor/shipments/'.$other->tracking_number.$suffix)->assertNotFound();
        }
    }

    public function test_telemetry_returns_latest_page_chronologically_and_stale_marker(): void
    {
        $vendor = User::factory()->create();
        $own = Shipment::factory()->create(['buyer_id' => $vendor->id]);
        foreach ([3, 2, 1] as $minutes) {
            TelemetrySample::create(['shipment_id' => $own->id, 'temperature' => $minutes + 3, 'latitude' => -6.2,
                'longitude' => 106.8, 'recorded_at' => now()->subMinutes($minutes), 'is_simulated' => true]);
        }
        Sanctum::actingAs($vendor, ['vendor']);
        $url = '/api/v1/vendor/shipments/'.$own->tracking_number.'/telemetry';
        $this->getJson($url.'?per_page=2')->assertOk()->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.temperature', 5)->assertJsonPath('data.1.temperature', 4)->assertJsonPath('meta.is_stale', false);
        $this->getJson($url.'?per_page=1001')->assertUnprocessable();
        $this->getJson($url.'?from=bad')->assertUnprocessable();
        $from = rawurlencode(now()->subSeconds(90)->toISOString());
        $this->getJson($url.'?from='.$from)->assertOk()->assertJsonCount(1, 'data');
        $this->travel(20)->minutes();
        $this->getJson($url)->assertOk()->assertJsonPath('meta.is_stale', true);
    }

    public function test_reports_are_scoped_pdf_downloads_with_verified_snapshot(): void
    {
        $vendor = User::factory()->create();
        $own = Shipment::factory()->create(['buyer_id' => $vendor->id, 'status' => 'completed']);
        $other = Shipment::factory()->create();
        $snapshot = ['tracking_number' => $own->tracking_number, 'notes' => '<script>alert(1)</script>'];
        $report = ShipmentReport::create(['shipment_id' => $own->id, 'title' => 'Laporan Demo', 'snapshot' => $snapshot,
            'sha256' => ShipmentReport::hashSnapshot($snapshot), 'published_at' => now(), 'is_demo' => true]);
        $foreign = ShipmentReport::create(['shipment_id' => $other->id, 'title' => 'Other', 'snapshot' => [],
            'sha256' => ShipmentReport::hashSnapshot([]), 'published_at' => now()]);
        Sanctum::actingAs($vendor, ['vendor']);
        $this->getJson('/api/v1/vendor/shipments/'.$own->tracking_number.'/reports')->assertOk()->assertJsonCount(1, 'data');
        $this->getJson('/api/v1/vendor/reports/'.$report->id.'/verify')->assertOk()->assertJsonPath('data.integrity_valid', true);
        $pdf = $this->get('/api/v1/vendor/reports/'.$report->id.'/download');
        $pdf->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringStartsWith('%PDF-', $pdf->getContent());
        $this->getJson('/api/v1/vendor/reports/'.$foreign->id.'/download')->assertNotFound();
        $this->getJson('/api/v1/vendor/reports/'.$foreign->id.'/verify')->assertNotFound();
        DB::table('shipment_reports')->where('id', $report->id)->update(['sha256' => str_repeat('0', 64)]);
        $this->getJson('/api/v1/vendor/reports/'.$report->id.'/download')->assertStatus(409);
    }

    public function test_missing_photo_and_internal_path_are_not_exposed(): void
    {
        $vendor = User::factory()->create();
        $own = Shipment::factory()->create(['buyer_id' => $vendor->id]);
        Sanctum::actingAs($vendor, ['vendor']);
        $url = '/api/v1/vendor/shipments/'.$own->tracking_number;
        $this->getJson($url.'/inspection-photo')->assertNotFound();
        Inspection::create(['shipment_id' => $own->id, 'packaging' => true, 'seal' => false, 'label' => true,
            'passed' => false, 'temperature' => 0, 'note' => 'Segel rusak', 'inspected_at' => now(), 'photo_path' => '../../.env']);
        $this->getJson($url)->assertOk()->assertJsonPath('data.inspection.temperature', 0)
            ->assertJsonPath('data.inspection.passed', false)->assertJsonMissingPath('data.inspection.photo_path');
        $this->getJson($url.'/inspection-photo')->assertNotFound();
    }

    public function test_inspection_photo_is_private_image_response(): void
    {
        Storage::fake('local');
        $vendor = User::factory()->create();
        $own = Shipment::factory()->create(['buyer_id' => $vendor->id]);
        $image = UploadedFile::fake()->image('sample.png');
        $bytes = file_get_contents($image->path());
        Storage::disk('local')->put('inspections/example.png', $bytes);
        Inspection::create(['shipment_id' => $own->id, 'packaging' => true, 'seal' => true, 'label' => true,
            'passed' => true, 'note' => 'Demo', 'inspected_at' => now(), 'photo_path' => 'inspections/example.png']);
        Sanctum::actingAs($vendor, ['vendor']);
        $this->get('/api/v1/vendor/shipments/'.$own->tracking_number.'/inspection-photo')->assertOk()->assertHeader('Content-Type', 'image/png');
    }

    public function test_guest_and_admin_cannot_use_monitor(): void
    {
        $shipment = Shipment::factory()->create();
        $url = '/api/v1/vendor/shipments/'.$shipment->tracking_number;
        $this->getJson($url)->assertUnauthorized();
        Sanctum::actingAs(User::factory()->create(['role' => 'admin']));
        $this->getJson($url)->assertForbidden();
    }
}
