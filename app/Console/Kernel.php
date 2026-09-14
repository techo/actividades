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
        // Recordatorio proactivo de pago: ~2 días antes de vencer la fecha límite.
        $schedule->command('pago:recordatorio-vencimiento')->dailyAt('09:30');
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

        // Poda de Telescope: retención de 48h. En sandbox (APP_ENV=local) Telescope
        // queda ON y sin poda llegó a 5.2M filas / ~2.4G (sep-2026), más grande que
        // toda la BD de prod. Se agenda solo en local: es el único entorno donde el
        // TelescopeServiceProvider se registra (ver AppServiceProvider::register), así
        // que en prod el comando 'telescope:prune' ni siquiera existe. Viaja con el
        // scheduler ya instalado en ambos entornos; no necesita cron/sudo aparte.
        if ($this->app->isLocal()) {
            $schedule->command('telescope:prune --hours=48')->daily();
        }
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
