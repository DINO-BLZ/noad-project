<?php

use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\RedirectAdminFromShop;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withSchedule(function (Schedule $schedule): void {
        $schedule->command('drops:notify-opened')
            ->everyMinute()
            ->timezone(config('app.timezone'))
            ->withoutOverlapping();

        $schedule->command('drops:expire-pending-whitelists')
            ->everyMinute()
            ->timezone(config('app.timezone'))
            ->withoutOverlapping();

        $schedule->command('carts:purge-old-guests')
            ->dailyAt('03:30')
            ->timezone(config('app.timezone'))
            ->withoutOverlapping();

        $schedule->command('auth:purge-old-password-reset-tokens')
            ->dailyAt('04:00')
            ->timezone(config('app.timezone'))
            ->withoutOverlapping();

        $schedule->command('search:reindex-products')
            ->dailyAt('03:00')
            ->timezone(config('app.timezone'))
            ->withoutOverlapping();

        $schedule->command('orders:send-daily-digest')
            ->dailyAt('07:00')
            ->timezone(config('app.timezone'))
            ->withoutOverlapping();
    })
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => EnsureUserIsAdmin::class,
        ]);

        $middleware->web(append: [
            RedirectAdminFromShop::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create();
