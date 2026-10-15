<?php

use App\CourierTelemetryService;
use App\TelegramOutbox;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
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

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
