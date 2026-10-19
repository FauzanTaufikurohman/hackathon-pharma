<?php

namespace Tests\Feature;

use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WhatsAppServiceTest extends TestCase
{
    public function test_it_sends_a_text_message_using_the_cloud_api(): void
    {
        config([
            'services.whatsapp.version' => 'v23.0',
            'services.whatsapp.phone_number_id' => '123456789',
            'services.whatsapp.access_token' => 'test-token',
        ]);

        Http::fake([
            'graph.facebook.com/*' => Http::response([
                'messaging_product' => 'whatsapp',
                'messages' => [
                    ['id' => 'wamid.test-message'],
                ],
            ], 200),
        ]);

        $result = app(WhatsAppService::class)->sendText(
            '6281234567890',
            'Tes notifikasi Anteraja Pharma'
        );

        $this->assertSame(
            'wamid.test-message',
            $result['messages'][0]['id']
        );

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'graph.facebook.com')
                && $request['to'] === '6281234567890'
                && $request['type'] === 'text';
        });
    }

    public function test_it_uploads_a_pdf_and_sends_it_with_an_approved_template(): void
    {
        config([
            'services.whatsapp.version' => 'v23.0',
            'services.whatsapp.phone_number_id' => '123456789',
            'services.whatsapp.access_token' => 'test-token',
        ]);

        Http::fakeSequence()
            ->push(['id' => 'media.test-pdf'], 200)
            ->push([
                'messaging_product' => 'whatsapp',
                'messages' => [['id' => 'wamid.test-report']],
            ], 200);

        $result = app(WhatsAppService::class)->sendDocumentTemplate(
            '+62 812-3456-7890',
            '%PDF-1.4 test report',
            'shipment-AP-123.pdf',
            'shipment_report',
            'id',
            ['AP-123', 'DELIVERED'],
        );

        $this->assertSame(
            'wamid.test-report',
            $result['messages'][0]['id']
        );

        Http::assertSentCount(2);
        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/media')
                && str_contains($request->body(), 'shipment-AP-123.pdf')
                && str_contains($request->body(), '%PDF-1.4 test report');
        });
        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/messages')
                && $request['type'] === 'template'
                && $request['template']['name'] === 'shipment_report'
                && $request['template']['components'][0]['parameters'][0]['document']['id']
                    === 'media.test-pdf'
                && $request['template']['components'][1]['parameters'][0]['text']
                    === 'AP-123';
        });
    }
}