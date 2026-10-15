<?php

namespace Tests\Feature;

use App\CourierReportService;
use App\Models\Message;
use App\Models\Shipment;
use App\Models\ShipmentReport;
use App\Models\User;
use App\ReportDistribution;
use App\TelegramOutbox;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TelegramReportAttachmentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
        config(['services.telegram.enabled' => true, 'services.telegram.bot_token' => '123:test-token',
            'services.telegram.frontend_url' => 'http://localhost:5173']);
    }

    private function report(bool $demo = false): ShipmentReport
    {
        $buyer = User::factory()->create();
        $buyer->telegram_chat_id = '12345';
        $buyer->save();
        $shipment = Shipment::factory()->create(['buyer_id' => $buyer->id, 'is_demo' => $demo,
            'status' => 'completed', 'completed_at' => now()]);

        return app(CourierReportService::class)->publish($shipment);
    }

    private function acknowledge(): void
    {
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true, 'result' => [
            'message_id' => 101, 'document' => ['file_id' => 'telegram-document-test'],
        ]])]);
    }

    public function test_published_report_queues_once_and_uploads_real_pdf_with_caption_and_checksum(): void
    {
        $this->acknowledge();
        $report = $this->report();
        app(ReportDistribution::class)->queue($report);
        $this->assertDatabaseCount('messages', 1);
        $message = Message::firstOrFail();
        $this->assertSame($report->id, $message->shipment_report_id);
        app(TelegramOutbox::class)->dispatch();
        app(TelegramOutbox::class)->dispatch();
        Http::assertSentCount(1);
        Http::assertSent(function ($request) use ($message, $report): bool {
            $data = collect($request->data())->keyBy('name');
            $pdf = $data->get('document')['contents'] ?? '';

            return str_ends_with($request->url(), '/sendDocument')
                && $request->hasFile('document', null, 'BAP-'.$report->id.'-R1.pdf')
                && str_starts_with($pdf, '%PDF-')
                && $data->get('chat_id')['contents'] === '12345'
                && str_contains($data->get('caption')['contents'], '/vendor/monitor/')
                && strlen($message->fresh()->attachment_sha256) === 64
                && hash_equals(hash('sha256', $pdf), $message->fresh()->attachment_sha256);
        });
        $this->assertSame('sent', $message->fresh()->status);
        $this->assertSame('101', $message->fresh()->provider_message_id);
    }

    public function test_legacy_queued_report_is_upgraded_to_document_instead_of_only_text(): void
    {
        $this->acknowledge();
        $report = $this->report();
        $message = Message::firstOrFail();
        $message->update(['shipment_report_id' => null]);
        app(TelegramOutbox::class)->dispatch();
        $this->assertSame($report->id, $message->fresh()->shipment_report_id);
        $this->assertSame('sent', $message->fresh()->status);
        Http::assertSent(fn ($request) => str_ends_with($request->url(), '/sendDocument'));
    }

    public function test_tampered_and_cross_shipment_report_never_reach_provider(): void
    {
        Http::fake();
        $report = $this->report();
        $message = Message::firstOrFail();
        DB::table('shipment_reports')->where('id', $report->id)->update(['sha256' => str_repeat('0', 64)]);
        app(TelegramOutbox::class)->dispatch();
        $this->assertSame('report_integrity_failed', $message->fresh()->error_code);
        DB::table('shipment_reports')->where('id', $report->id)->update(['sha256' => $report->sha256]);
        $other = Shipment::factory()->create(['buyer_id' => $message->user_id, 'is_demo' => false]);
        $message->update(['status' => 'pending', 'shipment_id' => $other->id]);
        app(TelegramOutbox::class)->dispatch();
        $this->assertSame('report_integrity_failed', $message->fresh()->error_code);
        Http::assertNothingSent();
    }

    public function test_demo_or_revoked_recipient_cannot_receive_report(): void
    {
        Http::fake();
        $this->report(true);
        app(TelegramOutbox::class)->dispatch();
        $this->assertSame('skipped_demo', Message::firstOrFail()->status);
        $report = $this->report();
        $message = Message::where('shipment_report_id', $report->id)->firstOrFail();
        $shipment = Shipment::findOrFail($report->shipment_id);
        $shipment->update(['buyer_id' => User::factory()->create()->id]);
        app(TelegramOutbox::class)->dispatch();
        $this->assertSame('recipient_access_revoked', $message->fresh()->error_code);
        Http::assertNothingSent();
    }

    public function test_document_rate_limit_waits_and_timeout_never_claims_delivery_or_auto_repeats(): void
    {
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => false, 'parameters' => ['retry_after' => 60]], 429)]);
        $this->report();
        $message = Message::firstOrFail();
        app(TelegramOutbox::class)->dispatch();
        $this->assertSame('retry', $message->fresh()->status);
        app(TelegramOutbox::class)->dispatch();
        Http::assertSentCount(1);
        $message->update(['next_attempt_at' => now()->subSecond()]);
        Http::fake(fn () => throw new ConnectionException('uncertain upload'));
        app(TelegramOutbox::class)->dispatch();
        $this->assertSame('uncertain', $message->fresh()->status);
        $this->assertNull($message->fresh()->sent_at);
        $this->assertSame(0, app(TelegramOutbox::class)->dispatch()['processed']);
    }

    public function test_missing_document_ack_and_5xx_are_not_falsely_sent(): void
    {
        $this->report();
        $message = Message::firstOrFail();
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true, 'result' => ['message_id' => 2]])]);
        app(TelegramOutbox::class)->dispatch();
        $this->assertSame('uncertain', $message->fresh()->status);
        $message->update(['status' => 'pending']);
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => false], 503)]);
        app(TelegramOutbox::class)->dispatch();
        $this->assertSame('uncertain', $message->fresh()->status);
        $this->assertNull($message->fresh()->sent_at);
    }

    public function test_bot_check_only_lists_opt_in_private_chats_without_token_or_message_contents(): void
    {
        Http::fake([
            'api.telegram.org/*/getMe' => Http::response(['ok' => true, 'result' => ['username' => 'test_pharma_bot']]),
            'api.telegram.org/*/getUpdates*' => Http::response(['ok' => true, 'result' => [
                ['message' => ['chat' => ['type' => 'private', 'id' => 12345], 'text' => '/start']],
                ['message' => ['chat' => ['type' => 'group', 'id' => -9], 'text' => '/start']],
                ['message' => ['chat' => ['type' => 'private', 'id' => 99], 'text' => 'private message']],
            ]]),
        ]);
        $this->artisan('telegram:check', ['--starts' => true])
            ->expectsOutput('Bot terverifikasi: @test_pharma_bot')->expectsOutput('Pengiriman: aktif')
            ->expectsOutput('Chat ID: 12345')->assertExitCode(0);
        Http::assertSentCount(2);
    }

    public function test_cli_mapping_requires_active_known_account_and_explicit_replacement(): void
    {
        $user = User::factory()->create();
        $this->artisan('telegram:link', ['email' => $user->email, 'chat_id' => '12345'])->assertExitCode(0);
        $this->assertSame('12345', $user->fresh()->telegram_chat_id);
        $this->artisan('telegram:link', ['email' => $user->email, 'chat_id' => '67890'])->assertExitCode(1);
        $this->assertSame('12345', $user->fresh()->telegram_chat_id);
        $this->artisan('telegram:link', ['email' => $user->email, 'chat_id' => '67890', '--replace' => true])->assertExitCode(0);
        $this->artisan('telegram:link', ['email' => 'missing@example.test', 'chat_id' => '12345'])->assertExitCode(1);
        $this->artisan('telegram:link', ['email' => $user->email, 'chat_id' => '-12345'])->assertExitCode(1);
        $user->update(['is_active' => false]);
        $this->artisan('telegram:link', ['email' => $user->email, 'chat_id' => '12345', '--replace' => true])->assertExitCode(1);
    }

    public function test_diagnostic_pdf_requires_explicit_confirmation_and_contains_no_shipment_data(): void
    {
        $this->acknowledge();
        $this->artisan('telegram:test', ['chat_id' => '12345'])->assertExitCode(1);
        Http::assertNothingSent();
        $this->artisan('telegram:test', ['chat_id' => '12345', '--confirm' => true])->assertExitCode(0);
        Http::assertSent(fn ($request) => $request->hasFile('document', null, 'uji-koneksi-pharma.pdf'));
        Http::assertSentCount(1);
        $this->assertDatabaseCount('shipments', 0);
        $this->assertDatabaseCount('messages', 0);
    }
}
