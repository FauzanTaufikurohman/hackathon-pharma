<?php

use App\CourierTelemetryService;
use App\Models\User;
use App\TelegramOutbox;
use App\VendorReportService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

Artisan::command('courier:check-signals', function () {
    $this->info((string) app(CourierTelemetryService::class)->checkSignalLoss());
})->purpose('Check stale courier telemetry and hold affected shipments');

Schedule::call(fn () => app(CourierTelemetryService::class)->checkSignalLoss())->name('courier-signal-loss')->everyMinute()->withoutOverlapping();

Artisan::command('telegram:dispatch {--limit=20}', function () {
    $result = app(TelegramOutbox::class)->dispatch((int) $this->option('limit'));
    $this->info(json_encode($result));
})->purpose('Send queued Telegram messages without n8n');

Schedule::command('telegram:dispatch --limit=5')->name('telegram-outbox')->everyMinute()->withoutOverlapping()->runInBackground();

Artisan::command('telegram:check {--starts : Show private chat IDs that sent /start; do not acknowledge or link them}', function () {
    $token = config('services.telegram.bot_token');
    if (! $token) {
        $this->error('TELEGRAM_BOT_TOKEN belum diisi di server/.env.');

        return 1;
    }
    try {
        $base = 'https://api.telegram.org/bot'.$token;
        $request = fn () => Http::connectTimeout(3)->timeout(10)->withOptions(['allow_redirects' => false]);
        $response = $request()->get($base.'/getMe');
        if (! $response->successful() || $response->json('ok') !== true) {
            $this->error('Bot tidak dapat diverifikasi. Periksa token; HTTP '.$response->status().'.');

            return 1;
        }
        $this->info('Bot terverifikasi: @'.$response->json('result.username'));
        $this->info('Pengiriman: '.(config('services.telegram.enabled') ? 'aktif' : 'nonaktif'));
        if ($this->option('starts')) {
            $updates = $request()->get($base.'/getUpdates', ['timeout' => 0, 'limit' => 100, 'allowed_updates' => '["message"]']);
            if (! $updates->successful() || $updates->json('ok') !== true) {
                $this->error('Daftar /start tidak tersedia. Pastikan bot tidak memakai webhook/poller lain. HTTP '.$updates->status().'.');

                return 1;
            }
            $ids = collect($updates->json('result', []))->filter(fn (array $item) => data_get($item, 'message.chat.type') === 'private'
                && preg_match('/^\/start(?:@[a-zA-Z0-9_]+)?(?:\s|$)/', (string) data_get($item, 'message.text')))
                ->map(fn (array $item) => (string) data_get($item, 'message.chat.id'))->unique()->values();
            foreach ($ids as $id) {
                $this->line('Chat ID: '.$id);
            }
            if ($ids->isEmpty()) {
                $this->warn('Belum ada /start akun uji dalam update yang tersedia. Buka bot dan tekan Start.');
            }
        }

        return 0;
    } catch (Throwable $exception) {
        $curl = preg_match('/cURL error ([0-9]+)/', $exception->getMessage(), $matches) ? ' (cURL '.$matches[1].')' : '';
        $this->error('Tidak dapat menghubungi Telegram'.$curl.'. Periksa jaringan/CA TLS PHP lalu coba lagi. Token tidak dicetak.');

        return 1;
    }
})->purpose('Verify bot and inspect opt-in private /start chat IDs without exposing credentials');

Artisan::command('telegram:link {email} {chat_id} {--replace : Explicitly replace an existing mapping}', function () {
    $chat = (string) $this->argument('chat_id');
    if (! preg_match('/^[1-9][0-9]{0,19}$/', $chat)) {
        $this->error('Gunakan Chat ID pribadi numerik dari akun yang sudah menekan Start.');

        return 1;
    }
    $user = User::where('email', strtolower(trim((string) $this->argument('email'))))->where('is_active', true)->first();
    if (! $user) {
        $this->error('Akun aktif tidak ditemukan.');

        return 1;
    }
    if ($user->telegram_chat_id && $user->telegram_chat_id !== $chat && ! $this->option('replace')) {
        $this->error('Akun sudah memiliki mapping. Periksa penerima lalu gunakan --replace jika memang perlu.');

        return 1;
    }
    $user->telegram_chat_id = $chat;
    $user->save();
    Log::info('telegram.recipient_linked', ['user_id' => $user->id, 'role' => $user->role]);
    $this->info('Telegram dipetakan ke akun '.$user->email.'. Pesan belum dikirim.');

    return 0;
})->purpose('Explicitly map a consenting Telegram private chat to an existing application account');

Artisan::command('telegram:test {chat_id} {--confirm : Send one diagnostic PDF without shipment or personal data}', function () {
    $chat = (string) $this->argument('chat_id');
    if (! $this->option('confirm') || ! preg_match('/^[1-9][0-9]{0,19}$/', $chat) || ! config('services.telegram.bot_token')) {
        $this->error('Token dan Chat ID pribadi wajib tersedia. Tambahkan --confirm untuk mengirim satu PDF uji tanpa data kiriman.');

        return 1;
    }
    try {
        $pdf = app(VendorReportService::class)->pdf('Uji koneksi Telegram Pharma',
            '<p>Bot berhasil mengunggah dokumen PDF.</p><p>Ini hanya uji koneksi. Tidak ada data kiriman, pasien, atau akun aplikasi di dalam dokumen ini.</p>',
            'uji-koneksi-pharma.pdf')->getContent();
        $response = Http::connectTimeout(3)->timeout(30)->withOptions(['allow_redirects' => false])
            ->attach('document', $pdf, 'uji-koneksi-pharma.pdf', ['Content-Type' => 'application/pdf'])
            ->post('https://api.telegram.org/bot'.config('services.telegram.bot_token').'/sendDocument',
                ['chat_id' => $chat, 'caption' => 'Koneksi bot Pharma berhasil. PDF ini hanya uji koneksi, bukan laporan pengiriman. Notifikasi akun menunggu pemetaan akun oleh Admin.']);
        if (! $response->successful() || $response->json('ok') !== true || ! $response->json('result.document.file_id')) {
            $this->error('Upload belum terkonfirmasi; HTTP '.$response->status().'. Periksa Telegram sebelum mencoba lagi.');

            return 1;
        }
        $this->info('PDF uji diterima Telegram. Message ID: '.$response->json('result.message_id').'. Periksa chat bot di HP.');

        return 0;
    } catch (Throwable $exception) {
        $this->error('Pengiriman PDF belum terkonfirmasi. Periksa chat bot sebelum mencoba lagi; token tidak dicetak.');

        return 1;
    }
})->purpose('Send one explicitly confirmed diagnostic PDF without reading or sending application data');

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
