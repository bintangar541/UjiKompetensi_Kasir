<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Contoh: Jalankan command setiap hari jam 01:00 pagi
        // $schedule->command('nama:command')->dailyAt('01:00');

        // $schedule->command('inspire')->hourly(); // Default contoh
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }

    protected $routeMiddleware = [
        // middleware bawaan
        'admin' => \App\Http\Middleware\AdminMiddleware::class,
        'employee' => \App\Http\Middleware\EmployeeMiddleware::class,
    ];

}
