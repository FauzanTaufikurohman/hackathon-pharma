<?php

namespace Tests\Feature;

use App\Models\Shipment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminMonitorTest extends TestCase
{
    use RefreshDatabase;

    public function test_temperature_prediction_uses_recorded_samples_not_hardcoded_banner(): void
    {
        $this->freezeTime();
        $admin = User::factory()->create(['role' => 'admin']);
        $s = Shipment::factory()->create(['status' => 'in_transit']);
        Sanctum::actingAs($admin, ['admin']);
        $url = '/api/v1/admin/shipments/'.$s->tracking_number.'/prediction';
        $this->getJson($url)->assertOk()->assertJsonPath('data.available', false);
        foreach ([[4, 6], [2, 7], [0, 7.9]] as [$minutes, $temperature]) {
            $s->telemetry()->create(['temperature' => $temperature, 'recorded_at' => now()->subMinutes($minutes), 'is_simulated' => true]);
        }
        $this->getJson($url)->assertOk()->assertJsonPath('data.available', true)->assertJsonPath('data.warning', true)->assertJsonPath('data.is_simulated', true);
        $this->travel(3)->minutes();
        $this->getJson($url)->assertOk()->assertJsonPath('data.available', false);
    }

    public function test_ai_advice_sends_only_numeric_telemetry_and_returns_a_bounded_operator_recommendation(): void
    {
        $this->freezeTime();
        Http::preventStrayRequests();
        Http::fake(['api.openai.com/*' => Http::response(['choices' => [['message' => ['content' => json_encode([
            'urgency' => 'review',
            'summary' => 'Periksa kondisi sensor.',
            'actions' => ['Verifikasi sensor dan SOP.'],
        ])]]]])]);
        config(['services.openai.key' => 'test-key', 'services.openai.model' => 'gpt-test']);
        $admin = User::factory()->create(['role' => 'admin']);
        $shipment = Shipment::factory()->create([
            'tracking_number' => 'PRIVATE-AWB-991',
            'sender_name' => 'Private sender name',
            'recipient_name' => 'Private recipient name',
            'destination_city' => 'Private destination',
            'temperature_min' => 2,
            'temperature_max' => 8,
        ]);
        foreach ([[4, 5.0], [2, 5.5], [0, 6.0]] as [$minutes, $temperature]) {
            $shipment->telemetry()->create([
                'temperature' => $temperature,
                'latitude' => 12.5,
                'longitude' => 90.2,
                'recorded_at' => now()->subMinutes($minutes),
                'is_simulated' => false,
            ]);
        }
        Sanctum::actingAs($admin, ['admin']);

        $this->getJson('/api/v1/admin/shipments/'.$shipment->tracking_number.'/advice')
            ->assertOk()
            ->assertJsonPath('data.available', true)
            ->assertJsonPath('data.urgency', 'review')
            ->assertJsonPath('data.actions.0', 'Verifikasi sensor dan SOP.');

        Http::assertSent(function (ClientRequest $request) {
            $content = json_decode($request['messages'][1]['content'], true);
            $encoded = json_encode($content);

            return $request->url() === 'https://api.openai.com/v1/chat/completions'
                && $request['store'] === false
                && $content['safe_range_c'] === ['minimum' => 2, 'maximum' => 8]
                && count($content['recent_samples']) === 3
                && ! str_contains($encoded, 'PRIVATE-AWB-991')
                && ! str_contains($encoded, 'Private sender name')
                && ! str_contains($encoded, 'Private recipient name')
                && ! str_contains($encoded, 'Private destination')
                && ! str_contains($encoded, 'latitude')
                && ! str_contains($encoded, 'longitude');
        });
    }

    public function test_ai_advice_reports_provider_failure_without_claiming_success(): void
    {
        $this->freezeTime();
        Http::preventStrayRequests();
        Http::fake(['api.openai.com/*' => Http::response([], 503)]);
        config(['services.openai.key' => 'test-key']);
        $admin = User::factory()->create(['role' => 'admin']);
        $shipment = Shipment::factory()->create(['temperature_min' => 2, 'temperature_max' => 8]);
        foreach ([[4, 5.0], [2, 5.5], [0, 6.0]] as [$minutes, $temperature]) {
            $shipment->telemetry()->create([
                'temperature' => $temperature,
                'recorded_at' => now()->subMinutes($minutes),
            ]);
        }
        Sanctum::actingAs($admin, ['admin']);

        $this->getJson('/api/v1/admin/shipments/'.$shipment->tracking_number.'/advice')
            ->assertOk()
            ->assertJsonPath('data.available', false)
            ->assertJsonPath('data.reason', 'Provider AI menolak permintaan. Periksa konfigurasi server dan coba lagi.');
    }
}
