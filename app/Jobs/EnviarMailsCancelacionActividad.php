<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Mail\CancelacionActividad;
use App\Services\MailerSes;

class EnviarMailsCancelacionActividad implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public $inscripcion;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $persona, array $actividad, array $pais)
    {

        $this->persona = \App\Persona::make($persona);
        $this->actividad = \App\Actividad::make($actividad);
        $this->pais = \App\Pais::make($pais);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if($this->persona->recibirMails && $this->persona->tieneMailValido()){
            // Por SES: la cancelación le escribe a todos los inscriptos (envío masivo),
            // Gmail corta esas ráfagas. Transaccional → no respeta bulk_pausado.
            MailerSes::enviar(new CancelacionActividad($this->persona, $this->actividad, $this->pais), $this->persona->mail);
        }
        //sleep(3);
    }
}
