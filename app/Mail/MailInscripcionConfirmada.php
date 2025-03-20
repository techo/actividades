<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class MailInscripcionConfirmada extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $inscripcion;
    public $persona;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($inscripcion)
    {
        $this->inscripcion = $inscripcion;
        $this->persona = $inscripcion->persona;
    
    }

    public function getQrUrl()
    {
        return env("APP_URL").'/admin/actividades/'.$this->inscripcion->idActividad.'/inscripcion/'.$this->inscripcion->idInscripcion.'/persona/'.$this->inscripcion->idPersona; 
    }
 
    public function build()
    {
        $qrImage = QrCode::format('png')->size(200)->generate($this->getQrUrl());
        $qrCid = $this->embedData($qrImage, 'qrcode.png', 'image/png');
        Log::info('QR Cid generado: ' . $qrCid);

        return $this
            ->subject( __('email.inscription_confirmed_title') . ' ' . $this->inscripcion->actividad->nombreActividad)
            ->from('noreplyactividades@techo.org')
            ->view('emails.inscripcionConfirmada')
            ->with([
                'inscripcion' => $this->inscripcion,
                'persona' => $this->persona,
            ]);
    }
}
