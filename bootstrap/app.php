<?php

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // apply our active-user check to all web requests
        $middleware->web([
            \App\Http\Middleware\VerifyCsrfToken::class,
            \App\Http\Middleware\CheckUserIsActive::class,
        ]);
    })
    ->withSchedule(function (Schedule $schedule): void {
        $schedule->command('app:send-payment-notifications')->dailyAt('09:00');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
