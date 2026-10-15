<?php

namespace Tests\Feature;

use App\Models\Message;
use App\Models\Shipment;
use App\Models\TelegramLinkRequest;
use App\Models\User;
use App\TelegramLinkService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TelegramLinkTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
        config(['services.telegram.enabled' => true, 'services.telegram.bot_token' => '123:test-token']);
    }

    public function test_buyer_can_create_a_short_lived_link_without_exposing_a_chat_id(): void
    {
        Http::fake(['api.telegram.org/*/getMe' => Http::response([
            'ok' => true, 'result' => ['username' => 'pharma_test_bot'],
        ])]);
        $buyer = User::factory()->create(['role' => 'buyer']);
        Sanctum::actingAs($buyer, ['vendor']);

        $response = $this->postJson('/api/v1/vendor/telegram/link')->assertCreated();
        $url = $response->json('data.url');
        parse_str((string) parse_url($url, PHP_URL_QUERY), $query);
        $plainToken = $query['start'] ?? '';

        $this->assertStringStartsWith('https://t.me/pharma_test_bot?start=', $url);
        $this->assertSame(48, strlen($plainToken));
        $this->assertDatabaseHas('telegram_link_requests', [
            'user_id' => $buyer->id,
            'token_hash' => hash('sha256', $plainToken),
            'consumed_at' => null,
        ]);
        $this->getJson('/api/v1/vendor/telegram')->assertOk()
            ->assertJsonPath('data.connected', false);
        $this->assertNull($buyer->fresh()->telegram_chat_id);
    }

    public function test_starting_the_deep_link_connects_the_chat_to_the_requesting_buyer(): void
    {
        Http::fake(['api.telegram.org/*/getMe' => Http::response([
            'ok' => true, 'result' => ['username' => 'pharma_test_bot'],
        ])]);
        $buyer = User::factory()->create(['role' => 'buyer']);
        $shipment = Shipment::factory()->create(['buyer_id' => $buyer->id]);
        $queuedMessage = Message::create([
            'shipment_id' => $shipment->id,
            'user_id' => $buyer->id,
            'dedupe_key' => 'link-test:'.$shipment->id,
            'subject' => 'Kiriman diperbarui',
            'body' => $shipment->tracking_number,
            'status' => 'unconfigured',
            'error_code' => 'recipient_not_configured',
        ]);
        $link = app(TelegramLinkService::class)->createLink($buyer);
        parse_str((string) parse_url($link['url'], PHP_URL_QUERY), $query);
        $plainToken = $query['start'];

        Http::fake([
            'api.telegram.org/*/getUpdates*' => Http::response(['ok' => true, 'result' => [
                ['update_id' => 9001, 'message' => [
                    'chat' => ['id' => 123456789, 'type' => 'private'],
                    'from' => ['id' => 123456789],
                    'text' => '/start '.$plainToken,
                ]],
            ]]),
            'api.telegram.org/*/sendMessage' => Http::response(['ok' => true, 'result' => ['message_id' => 12]]),
        ]);

        $this->artisan('telegram:link-sync')->assertExitCode(0);

        $this->assertSame('123456789', $buyer->fresh()->telegram_chat_id);
        $this->assertNotNull(TelegramLinkRequest::where('user_id', $buyer->id)->firstOrFail()->consumed_at);
        $this->assertSame('pending', $queuedMessage->fresh()->status);
        $this->assertNull($queuedMessage->fresh()->error_code);
        $this->assertDatabaseHas('telegram_bot_state', ['id' => 1, 'last_update_id' => 9001]);
        Http::assertSent(fn ($request) => str_ends_with($request->url(), '/sendMessage')
            && $request['chat_id'] === '123456789'
            && str_contains($request['text'], 'berhasil dihubungkan'));
    }

    public function test_group_or_mismatched_sender_updates_cannot_link_an_account(): void
    {
        Http::fake(['api.telegram.org/*/getMe' => Http::response([
            'ok' => true, 'result' => ['username' => 'pharma_test_bot'],
        ])]);
        $buyer = User::factory()->create(['role' => 'buyer']);
        $link = app(TelegramLinkService::class)->createLink($buyer);
        parse_str((string) parse_url($link['url'], PHP_URL_QUERY), $query);
        $plainToken = $query['start'];
        Http::fake(['api.telegram.org/*/getUpdates*' => Http::response(['ok' => true, 'result' => [
            ['update_id' => 9010, 'message' => [
                'chat' => ['id' => -123, 'type' => 'group'], 'from' => ['id' => 123],
                'text' => '/start '.$plainToken,
            ]],
            ['update_id' => 9011, 'message' => [
                'chat' => ['id' => 123456789, 'type' => 'private'], 'from' => ['id' => 99],
                'text' => '/start '.$plainToken,
            ]],
        ]])]);

        $this->artisan('telegram:link-sync')->assertExitCode(0);

        $this->assertNull($buyer->fresh()->telegram_chat_id);
        Http::assertSentCount(1);
        Http::assertSent(fn ($request) => str_contains($request->url(), '/getUpdates'));
    }

    public function test_only_active_buyer_accounts_can_request_a_link(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin, ['admin']);

        $this->getJson('/api/v1/vendor/telegram')->assertForbidden();
        $this->postJson('/api/v1/vendor/telegram/link')->assertForbidden();
    }
}
