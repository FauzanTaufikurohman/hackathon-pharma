<?php

namespace App;

use App\Models\Shipment;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use JsonException;

class TemperatureAiAdvisor
{
    public function advise(Shipment $shipment, array $prediction): array
    {
        if (! $prediction['available'] || $shipment->temperature_min === null || $shipment->temperature_max === null) {
            return ['available' => false, 'reason' => 'Sampel dan rentang suhu belum cukup untuk meminta rekomendasi AI.'];
        }
        $key = config('services.openai.key');
        if (! is_string($key) || trim($key) === '') {
            return ['available' => false, 'reason' => 'Provider AI belum dikonfigurasi di server.'];
        }

        $samples = $shipment->telemetry()
            ->whereNotNull('temperature')
            ->where('recorded_at', '>=', now()->subMinutes(15))
            ->where('recorded_at', '<=', now())
            ->orderByDesc('recorded_at')
            ->limit(30)
            ->get(['temperature', 'recorded_at'])
            ->reverse()
            ->values()
            ->tap(function ($samples) {
                $latest = $samples->last()?->recorded_at;
                $samples->transform(fn ($sample) => [
                    'minutes_before_latest' => $latest ? round($latest->diffInSeconds($sample->recorded_at, true) / 60, 2) : 0,
                    'temperature_c' => (float) $sample->temperature,
                ]);
            })
            ->all();

        $input = [
            'safe_range_c' => [
                'minimum' => $shipment->temperature_min,
                'maximum' => $shipment->temperature_max,
            ],
            'recent_samples' => $samples,
            'trend' => [
                'slope_c_per_minute' => $prediction['slope_c_per_minute'] ?? null,
                'minutes_to_upper_limit' => $prediction['minutes_to_upper_limit'] ?? null,
                'warning' => $prediction['warning'] ?? false,
                'includes_simulated_samples' => $prediction['is_simulated'] ?? false,
            ],
        ];

        try {
            $response = Http::connectTimeout(3)
                ->timeout(15)
                ->withToken($key)
                ->acceptJson()
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => config('services.openai.model', 'gpt-4.1-mini'),
                    'store' => false,
                    'temperature' => 0,
                    'max_completion_tokens' => 300,
                    'response_format' => ['type' => 'json_object'],
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'Berikan rekomendasi operasional cold-chain yang konservatif berdasarkan angka telemetri saja. Anda bukan pengendali perangkat, bukan apoteker, dan bukan penentu kelayakan obat. Jangan menyatakan produk aman atau memberi diagnosis/keputusan medis. Jika sampel simulasi, nyatakan dengan jelas. Sarankan operator memverifikasi sensor dan SOP. Jawab JSON berisi urgency (normal, review, urgent), summary (maksimal 400 karakter), actions (array maksimal 3 string singkat).',
                        ],
                        [
                            'role' => 'user',
                            'content' => json_encode($input, JSON_THROW_ON_ERROR),
                        ],
                    ],
                ]);
        } catch (ConnectionException) {
            return ['available' => false, 'reason' => 'Provider AI tidak merespons. Coba lagi atau tinjau tren telemetri secara manual.'];
        }

        if (! $response->successful()) {
            return ['available' => false, 'reason' => 'Provider AI menolak permintaan. Periksa konfigurasi server dan coba lagi.'];
        }

        $content = $response->json('choices.0.message.content');
        if (! is_string($content)) {
            return ['available' => false, 'reason' => 'Provider AI mengembalikan respons yang tidak dapat dibaca.'];
        }

        try {
            $advice = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return ['available' => false, 'reason' => 'Provider AI mengembalikan format rekomendasi yang tidak valid.'];
        }

        if (! is_array($advice)
            || ! in_array($advice['urgency'] ?? null, ['normal', 'review', 'urgent'], true)
            || ! is_string($advice['summary'] ?? null)
            || mb_strlen($advice['summary']) > 400
            || ! is_array($advice['actions'] ?? null)
            || count($advice['actions']) > 3
            || collect($advice['actions'])->contains(fn ($action) => ! is_string($action) || mb_strlen($action) > 180)) {
            return ['available' => false, 'reason' => 'Provider AI mengembalikan rekomendasi di luar format yang diizinkan.'];
        }

        return [
            'available' => true,
            'provider' => 'openai',
            'model' => config('services.openai.model', 'gpt-4.1-mini'),
            'urgency' => $advice['urgency'],
            'summary' => $advice['summary'],
            'actions' => array_values($advice['actions']),
            'includes_simulated_samples' => (bool) ($prediction['is_simulated'] ?? false),
            'note' => 'Saran AI untuk ditinjau operator. Bukan kendali perangkat atau keputusan mutu/medis.',
        ];
    }
}
