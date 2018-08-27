<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Mail\RecordatorioActividad;
use Mail;


class EnviarMailsRecordatorioActividad implements ShouldQueue
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
        if($this->inscripcion && $this->inscripcion->persona->recibirMails) {
            Mail::to($this->inscripcion->persona->mail)->send(new RecordatorioActividad($this->inscripcion));
        }
    }
}
