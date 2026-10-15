<?php

namespace Tests\Feature;

use App\Models\Message;
use App\Models\Shipment;
use App\Models\User;
use App\TelegramOutbox;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TelegramOutboxTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
        config(['services.telegram.enabled' => true, 'services.telegram.bot_token' => '123:test-token',
            'services.telegram.frontend_url' => 'http://localhost:5173']);
    }

    private function message(bool $demo = false, ?string $chat = '12345'): Message
    {
        $buyer = User::factory()->create();
        $buyer->telegram_chat_id = $chat;
        $buyer->save();
        $shipment = Shipment::factory()->create(['buyer_id' => $buyer->id, 'is_demo' => $demo]);

        return Message::create(['shipment_id' => $shipment->id, 'user_id' => $buyer->id,
            'dedupe_key' => 'test:'.$shipment->id, 'subject' => 'Kiriman diperbarui', 'body' => $shipment->tracking_number, 'status' => 'pending']);
    }

    public function test_artisan_worker_and_signal_commands_resolve_services(): void
    {
        config(['services.telegram.enabled' => false]);
        $this->artisan('telegram:dispatch', ['--limit' => 1])
            ->expectsOutput('{"configured":false,"processed":0}')->assertExitCode(0);
        $this->artisan('courier:check-signals')->expectsOutput('0')->assertExitCode(0);
    }

    public function test_disabled_gateway_no_credentials_and_demo_never_send_external_message(): void
    {
        Http::fake();
        $message = $this->message();
        config(['services.telegram.bot_token' => null]);
        $this->assertFalse(app(TelegramOutbox::class)->dispatch()['configured']);
        $this->assertSame('pending', $message->fresh()->status);
        config(['services.telegram.bot_token' => '123:test-token']);
        $demo = $this->message(true);
        $missing = $this->message(false, null);
        $message->update(['status' => 'failed']);
        app(TelegramOutbox::class)->dispatch();
        $this->assertSame('skipped_demo', $demo->fresh()->status);
        $this->assertSame('unconfigured', $missing->fresh()->status);
        Http::assertNothingSent();
    }

    public function test_provider_acknowledgement_marks_sent_once_and_includes_correct_deep_link(): void
    {
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true, 'result' => ['message_id' => 72]], 200)]);
        $message = $this->message();
        $worker = app(TelegramOutbox::class);
        $worker->dispatch();
        $worker->dispatch();
        $this->assertSame('sent', $message->fresh()->status);
        $this->assertSame('72', $message->fresh()->provider_message_id);
        $this->assertNotNull($message->fresh()->sent_at);
        Http::assertSentCount(1);
        Http::assertSent(fn ($request) => str_contains($request['text'], '/vendor/monitor/') && $request['chat_id'] === '12345');
    }

    public function test_rate_limit_waits_and_timeout_is_uncertain_not_falsely_sent(): void
    {
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => false, 'parameters' => ['retry_after' => 120]], 429)]);
        $message = $this->message();
        app(TelegramOutbox::class)->dispatch();
        $this->assertSame('retry', $message->fresh()->status);
        app(TelegramOutbox::class)->dispatch();
        Http::assertSentCount(1);
        $this->assertTrue($message->fresh()->next_attempt_at->isFuture());
        $message->update(['next_attempt_at' => now()->subMinute()]);
        Http::fake(fn () => throw new ConnectionException('transport timeout'));
        app(TelegramOutbox::class)->dispatch();
        $this->assertSame('uncertain', $message->fresh()->status);
        $this->assertNull($message->fresh()->sent_at);
        $this->assertSame(0, app(TelegramOutbox::class)->dispatch()['processed']);
    }
}
