<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('actividad:recordatorio')->dailyAt('08:00');
        $schedule->command('push:apertura-evaluacion')->dailyAt('09:00');
        $schedule->command('push:recordatorio-evaluacion')->dailyAt('09:00');
        $schedule->command('push:recordatorio-pago')->dailyAt('10:00');
        $schedule->command('push:reactivacion-voluntarios')->monthlyOn(1, '10:00');
        $schedule->command('reporting:sync-person-keys')->dailyAt('05:30');
        $schedule->command('reporting:snapshot-lifecycle')->monthlyOn(1, '06:00');
        // Red de seguridad para donaciones (PIX): sincroniza pending contra Stripe
        // por si un webhook se pierde. Idempotente; ->withoutOverlapping por las dudas.
        $schedule->command('donations:reconcile --commit')->hourly()->withoutOverlapping();

        // Recicla el worker de colas cada 30 min. El worker de larga vida reusa una
        // única conexión SMTP a Gmail que Google cierra tras un rato, y el próximo
        // envío falla con "fwrite(): SSL operation failed" (~175 mails/día a dead-letter,
        // sep-2026). queue:restart hace que el worker termine el job actual y salga;
        // Supervisor (autorestart=true) lo relanza con una conexión SMTP fresca.
        // No necesita cron/sudo aparte: viaja con este scheduler ya instalado en prod.
        $schedule->command('queue:restart')->everyThirtyMinutes();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
