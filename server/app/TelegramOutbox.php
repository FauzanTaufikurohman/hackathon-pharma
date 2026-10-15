<?php

namespace App;

use App\Models\Message;
use App\Models\Shipment;
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
            $text = $message->subject."\n".$message->body;
            $link = $this->link($recipient, $shipment);
            if ($link) {
                $text .= "\n".$link;
            }
            try {
                $response = Http::connectTimeout(2)->timeout(8)->withOptions(['allow_redirects' => false])
                    ->post('https://api.telegram.org/bot'.config('services.telegram.bot_token').'/sendMessage',
                        ['chat_id' => $recipient->telegram_chat_id, 'text' => mb_substr($text, 0, 4000), 'link_preview_options' => ['is_disabled' => true]]);
                $payload = $response->json();
                if ($response->successful() && ($payload['ok'] ?? false) === true && isset($payload['result']['message_id'])) {
                    $this->finish($message, $claim, ['status' => 'sent', 'sent_at' => now(), 'provider_message_id' => (string) $payload['result']['message_id']]);
                } elseif ($response->status() === 429 || (int) ($payload['error_code'] ?? 0) === 429) {
                    $delay = max(1, min(86400, (int) ($payload['parameters']['retry_after'] ?? 60)));
                    $this->retry($message, $claim, 'rate_limited', $delay);
                    break;
                } elseif ($response->serverError()) {
                    $this->retry($message, $claim, 'provider_unavailable', min(3600, 30 * (2 ** min(7, $message->attempts))));
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
