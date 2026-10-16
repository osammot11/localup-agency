<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('queue:work database --queue=lead-mail --stop-when-empty --max-jobs=25 --timeout=20 --tries=5 --backoff=60')
    ->everyMinute()
    ->withoutOverlapping(2)
    ->runInBackground();
Schedule::command('meta:retry')->everyFiveMinutes()->withoutOverlapping();
Schedule::command('sheets:sync')->everyFiveMinutes()->withoutOverlapping(15);
