<?php

namespace App\Services;

use App\Jobs\SendWhatsAppNotification;
use App\Jobs\SendShipmentReport;
use App\Models\Shipment;
use Illuminate\Support\Facades\Log;

class ShipmentWhatsAppNotifier
{
    public function notifyCustomer(
        Shipment $shipment,
        string $message
    ): bool {
        $shipment->loadMissing('customer');

        if (! $shipment->customer?->whatsapp_opt_in) {
            Log::warning('WhatsApp notification skipped: customer has not opted in.', [
                'shipment_id' => $shipment->id,
                'customer_id' => $shipment->customer_id,
            ]);

            return false;
        }

        $recipient = $shipment->customer?->whatsapp_number;

        if (! is_string($recipient) || trim($recipient) === '') {
            Log::warning('WhatsApp notification skipped: customer has no WhatsApp number.', [
                'shipment_id' => $shipment->id,
                'customer_id' => $shipment->customer_id,
            ]);

            return false;
        }

        SendWhatsAppNotification::dispatch(
            recipient: $recipient,
            message: $message,
            templateName: config('services.whatsapp.notification_template'),
            languageCode: config('services.whatsapp.language', 'id'),
            templateParameters: [mb_substr($message, 0, 1024)],
            customerId: $shipment->customer_id,
        )->afterCommit();

        return true;
    }

    public function notifyShipmentReport(Shipment $shipment): bool
    {
        $shipment->loadMissing([
            'customer',
            'originCustomerUser.customer',
            'destinationCustomerUser.customer',
        ]);
        $recipients = collect([
            $shipment->originCustomerUser?->customer,
            $shipment->destinationCustomerUser?->customer,
        ])->filter();

        if ($recipients->isEmpty() && $shipment->customer) {
            $recipients->push($shipment->customer);
        }

        $hasEligibleRecipient = $recipients->contains(
            fn ($customer) => $customer->whatsapp_opt_in
                && is_string($customer->whatsapp_number)
                && trim($customer->whatsapp_number) !== ''
        );

        if (! $hasEligibleRecipient) {
            Log::warning('Shipment report skipped: no shipment party has opted in with a WhatsApp number.', [
                'shipment_id' => $shipment->id,
            ]);

            return false;
        }

        SendShipmentReport::dispatch($shipment->id);

        return true;
    }

    public function notifyRejection(
        Shipment $shipment,
        string $reason
    ): bool {
        $shipment->loadMissing('customer');

        $message = sprintf(
            'Anteraja Pharma: shipment %s ditolak. Alasan: %s. Silakan hubungi tim operasional untuk informasi lebih lanjut.',
            $shipment->shipment_number,
            $reason
        );

        return $this->notifyCustomer($shipment, $message);
    }

    public function notifyIncidentResolved(
        Shipment $shipment,
        string $assessmentNote
    ): bool {
        $shipment->loadMissing('customer');

        $message = sprintf(
            'Anteraja Pharma: penanganan insiden suhu shipment %s telah diperbarui. Catatan penilaian: %s. Status ini bukan pengganti keputusan resmi kelayakan produk.',
            $shipment->shipment_number,
            $assessmentNote
        );

        return $this->notifyCustomer($shipment, $message);
    }
}