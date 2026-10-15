<?php

use App\CourierTelemetryService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('courier:check-signals', function () {
    $this->info((string) app(CourierTelemetryService::class)->checkSignalLoss());
})->purpose('Check stale courier telemetry and hold affected shipments');

Schedule::call(fn () => app(CourierTelemetryService::class)->checkSignalLoss())->name('courier-signal-loss')->everyMinute()->withoutOverlapping();

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
