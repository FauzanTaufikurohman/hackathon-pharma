<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Shipment;
use App\Models\ThermalCheck;
use App\Services\ThermalAssessmentService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ThermalAssessmentServiceTest extends TestCase
{
    public function test_it_requests_a_structured_advisory_from_gpt_4_1(): void
    {
        config([
            'services.openai.api_key' => 'test-openai-key',
            'services.openai.model' => 'gpt-4.1',
            'services.openai.base_url' => 'https://api.openai.com/v1',
            'services.openai.thermal_debug' => true,
        ]);

        Http::fake([
            'api.openai.com/v1/responses' => Http::response([
                'id' => 'resp-thermal-test',
                'model' => 'gpt-4.1',
                'usage' => ['input_tokens' => 100, 'output_tokens' => 50],
                'output' => [[
                    'type' => 'message',
                    'content' => [[
                        'type' => 'output_text',
                        'text' => json_encode([
                            'risk_level' => 'moderate',
                            'summary' => 'Suhu melebihi batas atas produk.',
                            'recommended_actions' => [
                                'Tahan shipment untuk penilaian checker.',
                            ],
                            'requires_human_review' => true,
                        ]),
                    ]],
                ]],
            ], 200, ['x-request-id' => 'req-thermal-test']),
        ]);

        $product = new Product([
            'name' => 'Produk pendingin',
            'min_temperature' => 2,
            'max_temperature' => 8,
            'max_excursion_seconds' => 3600,
            'temperature_protocol' => 'Simpan pada 2-8°C',
        ]);
        $shipment = new Shipment([
            'shipment_number' => 'AP-TEST-1',
        ]);
        $shipment->setRelation('product', $product);
        $check = new ThermalCheck([
            'temperature_celsius' => 9,
            'packaging_condition' => 'good',
            'physical_condition' => 'good',
            'checked_at' => now(),
        ]);
        $check->setRelation('shipment', $shipment);

        $service = app(ThermalAssessmentService::class);
        $assessment = $service->assessCheck($check);

        $this->assertSame('moderate', $assessment['risk_level']);
        $this->assertTrue($assessment['requires_human_review']);
        $debug = $service->debugDetails();
        $this->assertSame('completed', $debug['status']);
        $this->assertSame('req-thermal-test', $debug['provider_request_id']);
        $this->assertSame('resp-thermal-test', $debug['provider_response_id']);
        $this->assertSame(100, $debug['usage']['input_tokens']);
        $this->assertArrayNotHasKey('api_key', $debug);
        $this->assertStringContainsString(
            'bukan penilaian stabilitas',
            $assessment['disclaimer']
        );

        Http::assertSent(function ($request) {
            $input = json_decode(
                $request['input'][1]['content'][0]['text'],
                true
            );

            return $request['model'] === 'gpt-4.1'
                && $request->hasHeader('Authorization', 'Bearer test-openai-key')
                && (float) $input['observation']['temperature_celsius'] === 9.0;
        });
    }
}
