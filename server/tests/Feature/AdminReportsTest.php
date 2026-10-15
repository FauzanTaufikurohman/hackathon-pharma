<?php

namespace Tests\Feature;

use App\Models\Message;
use App\Models\Shipment;
use App\Models\ShipmentReport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminReportsTest extends TestCase
{
    use RefreshDatabase;

    public function test_report_download_distribution_is_verified_deduplicated_and_admin_only(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $shipment = Shipment::factory()->create(['status' => 'completed']);
        $snapshot = ['tracking_number' => $shipment->tracking_number, 'is_demo' => true];
        $report = ShipmentReport::create(['shipment_id' => $shipment->id, 'title' => 'Laporan uji', 'revision' => 1,
            'snapshot' => $snapshot, 'sha256' => ShipmentReport::hashSnapshot($snapshot), 'is_demo' => true, 'published_at' => now()]);
        Sanctum::actingAs($admin, ['admin']);
        $this->getJson('/api/v1/admin/reports')->assertOk()->assertJsonPath('total', 1);
        $pdf = $this->get('/api/v1/admin/reports/'.$report->id.'/download')->assertOk();
        $this->assertStringStartsWith('%PDF-', $pdf->getContent());
        $this->postJson('/api/v1/admin/reports/'.$report->id.'/distribute')->assertAccepted();
        $count = Message::count();
        $this->postJson('/api/v1/admin/reports/'.$report->id.'/distribute')->assertAccepted();
        $this->assertSame($count, Message::count());
        $this->getJson('/api/v1/admin/reports/daily?date=bad')->assertUnprocessable();
        $this->getJson('/api/v1/admin/notifications')->assertOk();
        Sanctum::actingAs($shipment->buyer, ['vendor']);
        $this->getJson('/api/v1/admin/reports')->assertForbidden();
        $this->postJson('/api/v1/admin/reports/'.$report->id.'/distribute')->assertForbidden();
    }
}
