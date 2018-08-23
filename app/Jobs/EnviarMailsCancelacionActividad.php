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
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $inscripcion;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($inscripcion)
    {
        $this->inscripcion = $inscripcion;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if($this->inscripcion->persona->recibirMails){
            Mail::to($this->inscripcion->persona->mail)->send(new CancelacionActividad($this->inscripcion));
        }
        //sleep(3);
    }
}
