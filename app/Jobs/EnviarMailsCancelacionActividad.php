<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Mail\CancelacionActividad;
use Mail;

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
        if($this->persona->recibirMails){
            Mail::to($this->persona->mail)->send(new CancelacionActividad($this->persona, $this->actividad, $this->pais));
        }
        //sleep(3);
    }
}
