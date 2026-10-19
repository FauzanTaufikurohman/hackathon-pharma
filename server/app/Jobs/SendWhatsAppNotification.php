<?php

namespace App\Jobs;

use App\Services\WhatsAppService;
use App\Models\Customer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendWhatsAppNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 30;

    public function backoff(): array
    {
        return [10, 30, 60];
    }

    public function __construct(
        public string $recipient,
        public string $message,
        public ?string $templateName = null,
        public string $languageCode = 'id',
        public array $templateParameters = [],
        public ?int $customerId = null,
    ) {
        $this->onQueue('notifications');
    }

    public function handle(WhatsAppService $whatsApp): void
    {
        if (
            $this->customerId !== null
            && ! Customer::whereKey($this->customerId)->value('whatsapp_opt_in')
        ) {
            Log::warning('WhatsApp notification skipped because opt-in is disabled.', [
                'customer_id' => $this->customerId,
            ]);

            return;
        }

        if ($this->templateName !== null) {
            $response = $whatsApp->sendTemplate(
                $this->recipient,
                $this->templateName,
                $this->languageCode,
                $this->templateParameters,
            );
        } else {
            $response = $whatsApp->sendText(
                $this->recipient,
                $this->message,
            );
        }

        Log::info('WhatsApp API accepted a notification', [
            'recipient' => $this->recipient,
            'message_id' => data_get($response, 'messages.0.id'),
        ]);
    }

    public function failed(?Throwable $exception): void
    {
        Log::error('WhatsApp notification job failed', [
            'recipient' => $this->recipient,
            'error' => $exception?->getMessage(),
        ]);
    }
}