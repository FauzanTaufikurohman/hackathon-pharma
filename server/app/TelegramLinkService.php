<?php

namespace App;

use App\Models\TelegramLinkRequest;
use App\Models\User;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TelegramLinkService
{
    /**
     * @return array{url: string, expires_at: string}
     */
    public function createLink(User $user): array
    {
        abort_unless(in_array($user->role, ['buyer', 'vendor'], true) && $user->is_active, 403);
        abort_if(filled($user->telegram_chat_id), 409, 'Akun Telegram sudah terhubung.');

        $token = config('services.telegram.bot_token');
        abort_unless(is_string($token) && $token !== '', 503, 'Integrasi Telegram belum dikonfigurasi.');

        try {
            $response = Http::connectTimeout(3)->timeout(8)->withOptions(['allow_redirects' => false])
                ->get('https://api.telegram.org/bot'.$token.'/getMe');
        } catch (ConnectionException) {
            abort(503, 'Bot Telegram belum dapat dihubungi. Coba lagi nanti.');
        }
        $username = $response->json('result.username');
        abort_unless($response->successful() && $response->json('ok') === true
            && is_string($username) && preg_match('/^[A-Za-z0-9_]{5,32}$/', $username), 503,
            'Bot Telegram belum dapat diverifikasi. Coba lagi nanti.');

        $plainToken = Str::random(48);
        $expiresAt = now()->addMinutes(15);
        TelegramLinkRequest::updateOrCreate(['user_id' => $user->id], [
            'token_hash' => hash('sha256', $plainToken),
            'expires_at' => $expiresAt,
            'consumed_at' => null,
        ]);

        return [
            'url' => 'https://t.me/'.$username.'?start='.$plainToken,
            'expires_at' => $expiresAt->toISOString(),
        ];
    }

    /**
     * @return array{ok: bool, processed: int, linked: int, error_code?: string}
     */
    public function syncUpdates(): array
    {
        $token = config('services.telegram.bot_token');
        if (! is_string($token) || $token === '') {
            return ['ok' => false, 'processed' => 0, 'linked' => 0, 'error_code' => 'bot_not_configured'];
        }

        $state = DB::table('telegram_bot_state')->where('id', 1)->first();
        $lastUpdateId = $state?->last_update_id === null ? null : (int) $state->last_update_id;
        $query = ['timeout' => 0, 'limit' => 100, 'allowed_updates' => '["message"]'];
        if ($lastUpdateId !== null) {
            $query['offset'] = $lastUpdateId + 1;
        }

        try {
            $response = Http::connectTimeout(3)->timeout(10)->withOptions(['allow_redirects' => false])
                ->get('https://api.telegram.org/bot'.$token.'/getUpdates', $query);
        } catch (ConnectionException) {
            Log::warning('telegram.link_updates_unreachable');

            return ['ok' => false, 'processed' => 0, 'linked' => 0, 'error_code' => 'telegram_unreachable'];
        }
        if (! $response->successful() || $response->json('ok') !== true || ! is_array($response->json('result'))) {
            Log::warning('telegram.link_updates_rejected', ['http_status' => $response->status()]);

            return ['ok' => false, 'processed' => 0, 'linked' => 0, 'error_code' => 'updates_unavailable'];
        }

        $processed = 0;
        $linked = 0;
        foreach ($response->json('result') as $update) {
            if (! is_array($update) || ! is_numeric($update['update_id'] ?? null)) {
                continue;
            }
            $updateId = (int) $update['update_id'];
            $lastUpdateId = max($lastUpdateId ?? $updateId, $updateId);
            $processed++;
            $result = $this->handleUpdate($update);
            if ($result !== null) {
                $linked += $result['linked'] ? 1 : 0;
                $this->sendConfirmation($result['chat_id'], $result['reply'], $token);
            }
        }

        if ($lastUpdateId !== null) {
            DB::table('telegram_bot_state')->updateOrInsert(['id' => 1], [
                'last_update_id' => $lastUpdateId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return ['ok' => true, 'processed' => $processed, 'linked' => $linked];
    }

    /**
     * @param  array<string, mixed>  $update
     * @return array{chat_id: string, reply: string, linked: bool}|null
     */
    private function handleUpdate(array $update): ?array
    {
        $message = $update['message'] ?? null;
        if (! is_array($message) || data_get($message, 'chat.type') !== 'private') {
            return null;
        }

        $chatId = (string) data_get($message, 'chat.id', '');
        $senderId = (string) data_get($message, 'from.id', '');
        if (! preg_match('/^[1-9][0-9]{0,19}$/', $chatId) || $senderId !== $chatId
            || ! preg_match('/^\/start(?:@[A-Za-z0-9_]+)?\s+([A-Za-z0-9_-]{16,64})$/', (string) ($message['text'] ?? ''), $matches)) {
            return null;
        }

        $reply = DB::transaction(function () use ($chatId, $matches): array {
            $linkRequest = TelegramLinkRequest::where('token_hash', hash('sha256', $matches[1]))
                ->lockForUpdate()->first();
            if (! $linkRequest || $linkRequest->consumed_at || ! $linkRequest->expires_at->isFuture()) {
                return ['reply' => 'Tautan tidak valid atau kedaluwarsa. Buat tautan baru dari portal penerima.', 'linked' => false];
            }

            $user = User::whereKey($linkRequest->user_id)->lockForUpdate()->first();
            if (! $user || ! $user->is_active || ! in_array($user->role, ['buyer', 'vendor'], true)) {
                $linkRequest->update(['consumed_at' => now()]);

                return ['reply' => 'Akun portal tidak dapat dihubungkan. Hubungi Admin.', 'linked' => false];
            }
            if (filled($user->telegram_chat_id) && $user->telegram_chat_id !== $chatId) {
                $linkRequest->update(['consumed_at' => now()]);

                return ['reply' => 'Akun portal sudah terhubung ke Telegram lain. Hubungi Admin untuk menggantinya.', 'linked' => false];
            }
            if (User::where('telegram_chat_id', $chatId)->where('id', '!=', $user->id)->exists()) {
                $linkRequest->update(['consumed_at' => now()]);

                return ['reply' => 'Akun Telegram ini sudah terhubung ke akun portal lain. Hubungi Admin.', 'linked' => false];
            }

            $user->telegram_chat_id = $chatId;
            $user->save();
            $linkRequest->update(['consumed_at' => now()]);
            DB::table('messages')->where('user_id', $user->id)->where('status', 'unconfigured')
                ->where('error_code', 'recipient_not_configured')
                ->update(['status' => 'pending', 'error_code' => null, 'next_attempt_at' => null, 'updated_at' => now()]);

            return ['reply' => 'Akun Pharma berhasil dihubungkan. Notifikasi kiriman akan dikirim ke chat ini.', 'linked' => true];
        });

        return ['chat_id' => $chatId, ...$reply];
    }

    private function sendConfirmation(string $chatId, string $text, string $token): void
    {
        try {
            $response = Http::connectTimeout(3)->timeout(8)->withOptions(['allow_redirects' => false])
                ->post('https://api.telegram.org/bot'.$token.'/sendMessage', ['chat_id' => $chatId, 'text' => $text]);
            if (! $response->successful() || $response->json('ok') !== true) {
                Log::warning('telegram.link_confirmation_rejected', ['http_status' => $response->status()]);
            }
        } catch (ConnectionException) {
            Log::warning('telegram.link_confirmation_unconfirmed');
        }
    }
}
