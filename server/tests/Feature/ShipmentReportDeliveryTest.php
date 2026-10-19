<?php

namespace Tests\Feature;

use App\Jobs\SendShipmentReport;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Shipment;
use App\Models\User;
use App\Services\ShipmentReportPdf;
use App\Services\WhatsAppService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ShipmentReportDeliveryTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_generates_a_pdf_and_delivers_it_through_meta_cloud_api(): void
    {
        config([
            'services.whatsapp.version' => 'v23.0',
            'services.whatsapp.phone_number_id' => '123456789',
            'services.whatsapp.access_token' => 'test-token',
            'services.whatsapp.report_template' => 'anteraja_pharma_shipment_report',
            'services.whatsapp.language' => 'id',
        ]);

        Http::fakeSequence()
            ->push(['id' => 'media.shipment-report'], 200)
            ->push(['messages' => [['id' => 'wamid.shipment-report']]], 200);

        $customer = Customer::create([
            'company_name' => 'Apotek Laporan',
            'company_type' => 'pharmacy',
            'whatsapp_number' => '6281234567890',
            'whatsapp_opt_in' => true,
        ]);
        $product = Product::create([
            'sku' => 'REPORT-COLD-1',
            'name' => 'Obat Suhu Dingin',
            'min_temperature' => 2,
            'max_temperature' => 8,
            'is_active' => true,
        ]);
        $shipment = Shipment::create([
            'shipment_number' => 'AP-REPORT-123',
            'customer_id' => $customer->id,
            'product_id' => $product->id,
            'quantity' => 3,
            'origin_address' => 'Gudang Produsen',
            'destination_address' => 'Apotek Tujuan',
            'status' => 'delivered',
            'delivered_at' => now(),
        ]);

        (new SendShipmentReport($shipment->id))->handle(
            app(ShipmentReportPdf::class),
            app(WhatsAppService::class),
        );

        Http::assertSentCount(2);
        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/media')
                && str_contains($request->body(), 'shipment-AP-REPORT-123.pdf')
                && str_contains($request->body(), '%PDF-');
        });
        Http::assertSent(function ($request) {
            if (! str_contains($request->url(), '/messages')) {
                return false;
            }

            $components = $request['template']['components'];

            return $request['template']['name'] === 'anteraja_pharma_shipment_report'
                && count($components) === 1
                && $components[0]['type'] === 'header'
                && $components[0]['parameters'][0]['document']['id']
                    === 'media.shipment-report';
        });
    }

    public function test_it_sends_the_pdf_report_to_both_opted_in_shipment_parties(): void
    {
        config([
            'services.whatsapp.version' => 'v23.0',
            'services.whatsapp.phone_number_id' => '123456789',
            'services.whatsapp.access_token' => 'test-token',
            'services.whatsapp.report_template' => 'anteraja_pharma_shipment_report',
            'services.whatsapp.language' => 'id',
        ]);

        Http::fakeSequence()
            ->push(['id' => 'media.report'], 200)
            ->push(['messages' => [['id' => 'wamid.origin']]], 200)
            ->push(['id' => 'media.report'], 200)
            ->push(['messages' => [['id' => 'wamid.destination']]], 200);

        $origin = $this->customerAccount('Produsen Laporan', 'manufacturer', '6281111111111');
        $destination = $this->customerAccount('Apotek Laporan', 'pharmacy', '6282222222222');
        $product = Product::create([
            'sku' => 'REPORT-BOTH-1',
            'name' => 'Obat Laporan Dua Pihak',
            'min_temperature' => 2,
            'max_temperature' => 8,
            'is_active' => true,
        ]);
        $shipment = Shipment::create([
            'shipment_number' => 'AP-REPORT-BOTH',
            'customer_id' => $origin->customer_id,
            'origin_customer_user_id' => $origin->id,
            'destination_customer_user_id' => $destination->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'origin_address' => 'Gudang produsen',
            'destination_address' => 'Apotek tujuan',
            'status' => 'delivered',
            'delivered_at' => now(),
        ]);

        (new SendShipmentReport($shipment->id))->handle(
            app(ShipmentReportPdf::class),
            app(WhatsAppService::class),
        );

        Http::assertSentCount(4);
        $messageRecipients = Http::recorded()
            ->filter(fn ($pair) => str_contains($pair[0]->url(), '/messages'))
            ->map(fn ($pair) => $pair[0]['to'])
            ->sort()
            ->values()
            ->all();
        $this->assertSame(['6281111111111', '6282222222222'], $messageRecipients);
    }

    private function customerAccount(string $companyName, string $companyType, string $phone): User
    {
        $customer = Customer::create([
            'company_name' => $companyName,
            'company_type' => $companyType,
            'whatsapp_number' => $phone,
            'whatsapp_opt_in' => true,
        ]);
        $user = User::factory()->create();
        $user->forceFill([
            'role' => 'customer',
            'customer_id' => $customer->id,
            'is_active' => true,
        ])->save();

        return $user;
    }
}
