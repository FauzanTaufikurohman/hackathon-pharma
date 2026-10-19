<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class WhatsAppService
{
    private function configValue(string $key): string
    {
        $value = config("services.whatsapp.{$key}");

        if (! is_string($value) || trim($value) === '') {
            throw new RuntimeException(
                "WhatsApp configuration '{$key}' is missing."
            );
        }

        return $value;
    }

    private function endpoint(): string
    {
        $version = $this->configValue('version');
        $phoneNumberId = $this->configValue('phone_number_id');

        return "https://graph.facebook.com/{$version}/{$phoneNumberId}/messages";
    }

    private function request(): PendingRequest
    {
        return Http::withToken($this->configValue('access_token'))
            ->acceptJson()
            ->asJson()
            ->connectTimeout(10)
            ->timeout(20);
    }

    /**
     * Send a free-form text message.
     * Normally usable during an open WhatsApp customer-service window.
     */
    public function sendText(string $to, string $message): array
    {
        $to = $this->normalizePhone($to);

        if (trim($message) === '') {
            throw new RuntimeException('WhatsApp message cannot be empty.');
        }

        $response = $this->request()->post($this->endpoint(), [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $to,
            'type' => 'text',
            'text' => [
                'preview_url' => false,
                'body' => $message,
            ],
        ]);

        return $this->responsePayload($response);
    }

    /**
     * Send an approved WhatsApp template.
     * Use this for eligible business-initiated notifications.
     */
    public function sendTemplate(
        string $to,
        string $templateName,
        string $languageCode = 'id',
        array $bodyParameters = [],
    ): array {
        $to = $this->normalizePhone($to);

        if (trim($templateName) === '') {
            throw new RuntimeException('Template name cannot be empty.');
        }

        $response = $this->request()->post($this->endpoint(), [
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'type' => 'template',
            'template' => $this->templatePayload(
                $templateName,
                $languageCode,
                $bodyParameters
            ),
        ]);

        return $this->responsePayload($response);
    }

    public function sendDocumentTemplate(
        string $to,
        string $document,
        string $fileName,
        string $templateName,
        string $languageCode = 'id',
        array $bodyParameters = [],
    ): array {
        $to = $this->normalizePhone($to);

        if (trim($document) === '') {
            throw new RuntimeException('WhatsApp document cannot be empty.');
        }

        if (trim($templateName) === '') {
            throw new RuntimeException('Template name cannot be empty.');
        }

        $fileName = basename($fileName);

        if (! str_ends_with(strtolower($fileName), '.pdf')) {
            throw new RuntimeException('WhatsApp report document must be a PDF.');
        }

        $phoneNumberId = $this->configValue('phone_number_id');
        $upload = Http::withToken($this->configValue('access_token'))
            ->acceptJson()
            ->connectTimeout(10)
            ->timeout(30)
            ->attach('file', $document, $fileName, [
                'Content-Type' => 'application/pdf',
            ])
            ->post(
                "https://graph.facebook.com/{$this->configValue('version')}/{$phoneNumberId}/media",
                [
                    'messaging_product' => 'whatsapp',
                    'type' => 'application/pdf',
                ]
            );

        $mediaId = $this->responsePayload($upload)['id'] ?? null;

        if (! is_string($mediaId) || $mediaId === '') {
            throw new RuntimeException('WhatsApp media upload returned no media ID.');
        }

        $payload = $this->templatePayload(
            $templateName,
            $languageCode,
            $bodyParameters
        );
        array_unshift($payload['components'], [
            'type' => 'header',
            'parameters' => [[
                'type' => 'document',
                'document' => [
                    'id' => $mediaId,
                    'filename' => $fileName,
                ],
            ]],
        ]);

        $response = $this->request()->post($this->endpoint(), [
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'type' => 'template',
            'template' => $payload,
        ]);

        return $this->responsePayload($response);
    }

    private function templatePayload(
        string $templateName,
        string $languageCode,
        array $bodyParameters,
    ): array {
        $components = [];

        if ($bodyParameters !== []) {
            $components[] = [
                'type' => 'body',
                'parameters' => array_map(
                    fn ($value) => [
                        'type' => 'text',
                        'text' => (string) $value,
                    ],
                    $bodyParameters
                ),
            ];
        }

        return [
            'name' => $templateName,
            'language' => ['code' => $languageCode],
            'components' => $components,
        ];
    }

    private function responsePayload(Response $response): array
    {
        $response->throw();

        $payload = $response->json();

        if (! is_array($payload)) {
            throw new RuntimeException('WhatsApp API returned an invalid response.');
        }

        return $payload;
    }

    private function normalizePhone(string $phone): string
    {
        // Supply the recipient in international format, e.g. 6281234567890.
        $normalized = preg_replace('/[\s()+-]/', '', trim($phone));

        if (
            ! is_string($normalized) ||
            ! preg_match('/^[1-9][0-9]{7,14}$/', $normalized)
        ) {
            throw new RuntimeException(
                'Recipient phone must use international format, e.g. 6281234567890.'
            );
        }

        return $normalized;
    }
}