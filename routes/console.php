<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('process:timedeposits')->everyMinute();
Schedule::command('savings:apply-quarterly-interest')->quarterlyOn(1, '00:05');
Schedule::command('reminders:time-deposit-maturity')->dailyAt('08:00');
