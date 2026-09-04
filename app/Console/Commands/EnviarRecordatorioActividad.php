<?php

namespace App\Console\Commands;

use App\Actividad;
use App\Services\Push\PushNotificationService;
use App\Jobs\EnviarMailsRecordatorioActividad;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Mail;

class EnviarRecordatorioActividad extends Command
{
    protected $signature = 'actividad:recordatorio';

    protected $description = 'Envia recordatorios a los usuario inscriptos en una actividad';

    protected $pushService;

    public function __construct(PushNotificationService $pushService)
    {
        parent::__construct();
        $this->pushService = $pushService;
    }

    public function handle()
    {
        $manana = Carbon::tomorrow();

        $actividades = Actividad::whereYear('fechaInicio', $manana->year)
                                ->whereMonth('fechaInicio', $manana->month)
                                ->whereDay('fechaInicio', $manana->day)
                                ->get();

        // Escalonado del batch: se despacha ~batch_por_minuto mails/min repartidos por un
        // delay incremental, para no disparar cientos de mails de golpe y saturar el relay
        // (Google corta la conexión bajo ráfaga). $i es global a todas las actividades.
        $porSegundo = max(1, intdiv((int) config('mailing.batch_por_minuto', 120), 60));
        $i = 0;

        foreach ($actividades as $actividad) {
            $hora = $actividad->fechaInicio ? $actividad->fechaInicio->format('H:i') : '';
            $inscripciones = $actividad->inscripciones()
                ->when($actividad->confirmacion == 1, function ($q) {
                    $q->where('confirma', 1);
                })
                ->when($actividad->pago == 1, function ($q) {
                    $q->where('pago', 1);
                })
                ->get();

            foreach ($inscripciones as $inscripcion) {
                $job = (new EnviarMailsRecordatorioActividad($inscripcion))->delay(5 + intdiv($i, $porSegundo));
                dispatch($job);
                $i++;

                $this->pushService->enviarLocalizado(
                    $inscripcion->persona,
                    'push.recordatorio_asistencia_titulo',
                    'push.recordatorio_asistencia_cuerpo',
                    ['actividad' => $actividad->nombreActividad, 'hora' => $hora],
                    ['tipo' => 'actividad', 'estado' => 'RECORDATORIO', 'idActividad' => $actividad->idActividad]
                );
            }
        }
    }
}
