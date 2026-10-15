<?php

namespace App;

use App\Models\Message;
use App\Models\Shipment;
use App\Models\ShipmentReport;
use App\Models\User;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class TelegramOutbox
{
    public function dispatch(int $limit = 20): array
    {
        if (! config('services.telegram.enabled') || ! config('services.telegram.bot_token')) {
            return ['configured' => false, 'processed' => 0];
        }
        Message::where('status', 'processing')->where('claimed_at', '<', now()->subMinutes(5))
            ->update(['status' => 'uncertain', 'error_code' => 'worker_interrupted', 'claim_token' => null]);
        $ids = Message::whereIn('status', ['pending', 'retry'])
            ->where(fn ($q) => $q->whereNull('next_attempt_at')->orWhere('next_attempt_at', '<=', now()))
            ->orderBy('id')->limit(max(1, min(100, $limit)))->pluck('id');
        $processed = 0;
        foreach ($ids as $id) {
            $claim = (string) Str::uuid();
            $message = DB::transaction(function () use ($id, $claim) {
                $record = Message::whereKey($id)->lockForUpdate()->first();
                if (! $record || ! in_array($record->status, ['pending', 'retry'], true)
                    || ($record->next_attempt_at && $record->next_attempt_at->isFuture())) {
                    return null;
                }
                $record->update(['status' => 'processing', 'claim_token' => $claim, 'claimed_at' => now(),
                    'attempts' => $record->attempts + 1, 'error_code' => null]);

                return $record;
            });
            if (! $message) {
                continue;
            }
            $processed++;
            $recipient = User::find($message->user_id);
            $shipment = Shipment::find($message->shipment_id);
            if (! $shipment || $shipment->is_demo) {
                $this->finish($message, $claim, ['status' => 'skipped_demo', 'error_code' => 'demo_not_sent']);

                continue;
            }
            if (! $recipient?->is_active || ! preg_match('/^-?[0-9]{1,20}$/', (string) $recipient->telegram_chat_id)) {
                $this->finish($message, $claim, ['status' => 'unconfigured', 'error_code' => 'recipient_not_configured']);

                continue;
            }
            if (! $this->canReceive($recipient, $shipment)) {
                $this->finish($message, $claim, ['status' => 'failed', 'error_code' => 'recipient_access_revoked']);

                continue;
            }
            $text = $message->subject."\n".$message->body;
            $link = $this->link($recipient, $shipment);
            if ($link) {
                $text .= "\n".$link;
            }
            try {
                $report = $this->report($message);
                if ($report && ($report->shipment_id !== $shipment->id
                    || ! hash_equals($report->sha256, $report->snapshotHash()))) {
                    $this->finish($message, $claim, ['status' => 'failed', 'error_code' => 'report_integrity_failed']);

                    continue;
                }
                if ($report?->is_demo) {
                    $this->finish($message, $claim, ['status' => 'skipped_demo', 'error_code' => 'demo_not_sent']);

                    continue;
                }
                $http = Http::connectTimeout(3)->timeout($report ? 30 : 8)->withOptions(['allow_redirects' => false]);
                $endpoint = 'https://api.telegram.org/bot'.config('services.telegram.bot_token');
                if ($report) {
                    $pdf = app(CourierReportService::class)->download($report)->getContent();
                    if (! is_string($pdf) || ! str_starts_with($pdf, '%PDF-') || strlen($pdf) > 10 * 1024 * 1024) {
                        $this->finish($message, $claim, ['status' => 'failed', 'error_code' => 'report_invalid_or_too_large']);

                        continue;
                    }
                    Message::whereKey($message->id)->where('claim_token', $claim)
                        ->update(['shipment_report_id' => $report->id, 'attachment_sha256' => hash('sha256', $pdf)]);
                    $response = $http->attach('document', $pdf, 'BAP-'.$report->id.'-R'.$report->revision.'.pdf', ['Content-Type' => 'application/pdf'])
                        ->post($endpoint.'/sendDocument', ['chat_id' => $recipient->telegram_chat_id,
                            'caption' => mb_substr($text, 0, 1000)]);
                } else {
                    $response = $http->post($endpoint.'/sendMessage', ['chat_id' => $recipient->telegram_chat_id,
                        'text' => mb_substr($text, 0, 4000), 'link_preview_options' => ['is_disabled' => true]]);
                }
                $payload = $response->json();
                if ($response->successful() && ($payload['ok'] ?? false) === true && isset($payload['result']['message_id'])
                    && (! $report || isset($payload['result']['document']['file_id']))) {
                    $this->finish($message, $claim, ['status' => 'sent', 'sent_at' => now(), 'provider_message_id' => (string) $payload['result']['message_id']]);
                } elseif ($response->status() === 429 || (int) ($payload['error_code'] ?? 0) === 429) {
                    $delay = max(1, min(86400, (int) ($payload['parameters']['retry_after'] ?? 60)));
                    $this->retry($message, $claim, 'rate_limited', $delay);
                    break;
                } elseif ($response->serverError() || $response->successful()) {
                    $this->finish($message, $claim, ['status' => 'uncertain', 'error_code' => 'provider_ack_unconfirmed']);
                } else {
                    $this->finish($message, $claim, ['status' => 'failed', 'error_code' => 'telegram_rejected_'.$response->status()]);
                }
            } catch (ConnectionException $exception) {
                $this->finish($message, $claim, ['status' => 'uncertain', 'error_code' => 'delivery_unconfirmed']);
            } catch (\Throwable $exception) {
                $this->finish($message, $claim, ['status' => 'failed', 'error_code' => 'delivery_failed']);
            }
        }

        return ['configured' => true, 'processed' => $processed];
    }

    private function canReceive(User $user, Shipment $shipment): bool
    {
        return match ($user->role) {
            'admin' => true,
            'buyer' => $user->id === $shipment->buyer_id,
            'courier' => $user->id === $shipment->courier_id,
            'checker' => $user->id === $shipment->inspector_id,
            default => false,
        };
    }

    private function report(Message $message): ?ShipmentReport
    {
        if ($message->shipment_report_id) {
            return ShipmentReport::findOrFail($message->shipment_report_id);
        }
        if (str_starts_with($message->dedupe_key, 'report:')) {
            abort_unless(preg_match('/^report:([0-9]+):([0-9]+)$/', $message->dedupe_key, $matches)
                && (int) $matches[2] === $message->user_id, 409);

            return ShipmentReport::findOrFail((int) $matches[1]);
        }

        return null;
    }

    private function link(User $user, Shipment $shipment): ?string
    {
        $origin = rtrim((string) config('services.telegram.frontend_url'), '/');
        if (! filter_var($origin, FILTER_VALIDATE_URL) || ! in_array(parse_url($origin, PHP_URL_SCHEME), ['http', 'https'], true)) {
            return null;
        }
        $awb = rawurlencode($shipment->tracking_number);
        $path = match ($user->role) {
            'courier' => '/courier/tugas/'.$awb,
            'checker' => '/checker/form-initial-exam?awb='.$awb,
            'admin' => '/admin/live-monitor-kendala?awb='.$awb,
            default => '/vendor/monitor/'.$awb,
        };

        return $origin.$path;
    }

    private function retry(Message $message, string $claim, string $error, int $seconds): void
    {
        $this->finish($message, $claim, ['status' => $message->attempts >= 5 ? 'failed' : 'retry',
            'error_code' => $error, 'next_attempt_at' => now()->addSeconds($seconds)]);
    }

    private function finish(Message $message, string $claim, array $values): void
    {
        Message::whereKey($message->id)->where('claim_token', $claim)->where('status', 'processing')
            ->update($values + ['claim_token' => null, 'updated_at' => now()]);
    }
}
