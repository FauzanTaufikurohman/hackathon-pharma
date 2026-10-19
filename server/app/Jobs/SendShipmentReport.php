<?php

namespace App\Jobs;

use App\Models\Shipment;
use App\Services\ShipmentReportPdf;
use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class SendShipmentReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 120;

    public function __construct(
        public int $shipmentId,
    ) {
        $this->onQueue('notifications');
    }

    public function backoff(): array
    {
        return [10, 30, 60];
    }

    public function handle(
        ShipmentReportPdf $reportPdf,
        WhatsAppService $whatsApp,
    ): void {
        $shipment = Shipment::with([
            'customer',
            'originCustomerUser.customer',
            'destinationCustomerUser.customer',
        ])->findOrFail($this->shipmentId);
        $customers = collect([
            $shipment->originCustomerUser?->customer,
            $shipment->destinationCustomerUser?->customer,
        ])->filter();
        if ($customers->isEmpty() && $shipment->customer) {
            $customers->push($shipment->customer);
        }
        $customers = $customers
            ->filter(fn ($customer) => $customer->whatsapp_opt_in)
            ->unique(fn ($customer) => $customer->whatsapp_number ?: 'customer-'.$customer->id)
            ->values();

        if ($customers->isEmpty()) {
            Log::warning('Shipment report skipped because WhatsApp opt-in is disabled.', [
                'shipment_id' => $shipment->id,
            ]);

            return;
        }

        $templateName = config('services.whatsapp.report_template');

        if (! is_string($templateName) || trim($templateName) === '') {
            throw new RuntimeException('WHATSAPP_REPORT_TEMPLATE is not configured.');
        }

        $pdf = $reportPdf->render($shipment);
        foreach ($customers as $customer) {
            $recipient = $customer->whatsapp_number;

            if (! is_string($recipient) || trim($recipient) === '') {
                Log::warning('Shipment report skipped for opted-in customer without WhatsApp number.', [
                    'shipment_id' => $shipment->id,
                    'customer_id' => $customer->id,
                ]);
                continue;
            }

            $response = $whatsApp->sendDocumentTemplate(
                $recipient,
                $pdf,
                $reportPdf->fileName($shipment),
                $templateName,
                config('services.whatsapp.language', 'id'),
            );

            Log::info('Shipment PDF report sent through WhatsApp.', [
                'shipment_id' => $shipment->id,
                'customer_id' => $customer->id,
                'message_id' => data_get($response, 'messages.0.id'),
            ]);
        }
    }

    public function failed(?Throwable $exception): void
    {
        Log::error('Shipment PDF WhatsApp job failed.', [
            'shipment_id' => $this->shipmentId,
            'error' => $exception?->getMessage(),
        ]);
    }
}
