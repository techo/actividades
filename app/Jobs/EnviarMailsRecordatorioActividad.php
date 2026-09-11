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
        if($this->inscripcion && $this->inscripcion->persona->recibirMails && $this->inscripcion->persona->tieneMailValido()) {
            // BULK → SES (mailer dedicado). Este job ya está encolado, así que el
            // envío sincrónico acá es correcto (el escalonado lo dio el delay del job).
            \App\Services\MailerSes::enviar(new RecordatorioActividad($this->inscripcion), $this->inscripcion->persona->mail);
        }
    }
}
