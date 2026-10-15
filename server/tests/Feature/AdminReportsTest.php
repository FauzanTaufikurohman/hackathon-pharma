<?php

namespace Tests\Feature;

use App\CourierReportService;
use App\Models\Message;
use App\Models\Shipment;
use App\Models\ShipmentReport;
use App\Models\TelemetrySample;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminReportsTest extends TestCase
{
    use RefreshDatabase;

    public function test_simulator_capability_matches_demo_status_and_role(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $demo = Shipment::factory()->create(['is_demo' => true, 'status' => 'in_transit']);
        $real = Shipment::factory()->create(['is_demo' => false, 'status' => 'in_transit']);
        Sanctum::actingAs($admin, ['admin']);
        $this->getJson('/api/v1/admin/shipments/'.$demo->tracking_number)->assertOk()->assertJsonPath('data.can_simulate', true);
        $this->getJson('/api/v1/admin/shipments/'.$real->tracking_number)->assertOk()->assertJsonPath('data.can_simulate', false);
        $demo->update(['status' => 'completed']);
        $this->getJson('/api/v1/admin/shipments/'.$demo->tracking_number)->assertOk()->assertJsonPath('data.can_simulate', false);
        Sanctum::actingAs($real->buyer, ['vendor']);
        $this->getJson('/api/v1/admin/shipments/'.$real->tracking_number)->assertForbidden();
    }

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

    public function test_admin_can_add_a_note_before_first_report_and_it_is_locked_into_the_pdf_snapshot(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $shipment = Shipment::factory()->create(['status' => 'completed', 'version' => 2]);
        Sanctum::actingAs($admin, ['admin']);
        $requestId = (string) Str::uuid();

        $this->postJson('/api/v1/admin/shipments/'.$shipment->tracking_number.'/report-notes', [
            'version' => 2,
            'request_id' => $requestId,
            'note' => 'Operator verified the temperature logger.',
        ])->assertOk()->assertJsonPath('data.version', 3);

        $this->assertDatabaseHas('events', [
            'shipment_id' => $shipment->id,
            'type' => 'report_note',
            'reason' => 'Operator verified the temperature logger.',
        ]);
        $report = app(CourierReportService::class)->publish($shipment->fresh());
        $noteEvent = collect($report->snapshot['events'])->firstWhere('type', 'report_note');
        $this->assertSame('Operator verified the temperature logger.', $noteEvent['reason']);
        $this->get('/api/v1/admin/reports/'.$report->id.'/download')->assertOk();

        $this->postJson('/api/v1/admin/shipments/'.$shipment->tracking_number.'/report-notes', [
            'version' => $shipment->fresh()->version,
            'request_id' => (string) Str::uuid(),
            'note' => 'This must not mutate a published report.',
        ])->assertStatus(409);

        $this->assertSame(1, $shipment->events()->where('type', 'report_note')->count());
        $this->assertSame($report->sha256, $report->fresh()->sha256);
    }

    public function test_telegram_diagnostics_report_mapping_counts_message_statuses_and_requested_page_without_exposing_chat_ids(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'telegram_chat_id' => '123456789']);
        $inactive = User::factory()->create(['role' => 'buyer', 'is_active' => false, 'telegram_chat_id' => '987654321']);
        $shipment = Shipment::factory()->create(['buyer_id' => $inactive->id]);
        for ($index = 1; $index <= 31; $index++) {
            Message::create([
                'shipment_id' => $shipment->id,
                'user_id' => $admin->id,
                'subject' => 'Queue item',
                'body' => 'Queue body',
                'status' => $index === 1 ? 'retry' : 'pending',
                'dedupe_key' => 'diagnostic-message-'.$index,
            ]);
        }
        config(['services.telegram.enabled' => true, 'services.telegram.bot_token' => 'test-token']);
        Sanctum::actingAs($admin, ['admin']);

        $this->getJson('/api/v1/admin/notifications?page=2')
            ->assertOk()
            ->assertJsonPath('gateway.configured', true)
            ->assertJsonPath('gateway.enabled', true)
            ->assertJsonPath('gateway.active_recipients', 1)
            ->assertJsonPath('gateway.pending_messages', 31)
            ->assertJsonPath('gateway.message_statuses.retry', 1)
            ->assertJsonPath('gateway.message_statuses.pending', 30)
            ->assertJsonPath('messages.current_page', 2)
            ->assertJsonCount(1, 'messages.data');

        $this->getJson('/api/v1/admin/users')
            ->assertOk()
            ->assertJsonPath('data.0.telegram_configured', true)
            ->assertJsonMissingPath('data.0.telegram_chat_id');
    }

    public function test_simulated_temperature_deviation_can_record_ai_correction_without_resuming_shipment(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $courier = User::factory()->create(['role' => 'courier']);
        $shipment = Shipment::factory()->assignedTo($courier, 'in_transit')->create([
            'temperature_min' => 2, 'temperature_max' => 8, 'is_demo' => true, 'version' => 1,
        ]);
        Sanctum::actingAs($admin, ['admin']);
        $url = '/api/v1/admin/shipments/'.$shipment->tracking_number.'/simulate';
        $this->freezeTime();
        $this->postJson($url, ['version' => $shipment->version, 'request_id' => (string) Str::uuid(),
            'type' => 'temperature', 'temperature' => 12])
            ->assertOk()->assertJsonPath('data.workflow_status', 'deviation_hold');
        $shipment->refresh();
        $this->assertDatabaseHas('issues', ['shipment_id' => $shipment->id, 'type' => 'temperature', 'status' => 'open']);
        $this->getJson('/api/v1/admin/shipments/'.$shipment->tracking_number)
            ->assertOk()->assertJsonPath('data.can_simulate', true);

        $correction = ['version' => $shipment->version, 'request_id' => (string) Str::uuid(),
            'type' => 'correct_temperature'];
        $this->postJson($url, $correction)
            ->assertOk()->assertJsonPath('data.workflow_status', 'deviation_hold');
        $latest = TelemetrySample::where('shipment_id', $shipment->id)->latest('id')->firstOrFail();
        $this->assertSame(5.0, (float) $latest->temperature);
        $this->assertSame('AI-SIMULATOR', $latest->sensor_id);
        $this->assertSame($latest->id, $shipment->fresh()->latestTelemetry->id);
        $this->assertSame('deviation_hold', $shipment->fresh()->status);
        $this->assertDatabaseHas('events', [
            'shipment_id' => $shipment->id, 'type' => 'simulation_correction',
        ]);

        $this->postJson($url, $correction)->assertOk()->assertJsonPath('replayed', true);
        $this->assertSame(6, TelemetrySample::where('shipment_id', $shipment->id)->count());
    }
}
