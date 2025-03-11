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
    public $QRCode;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($inscripcion)
    {
        $this->inscripcion = $inscripcion;
        $this->persona = $inscripcion->persona;
        $this->QRCode = $this->generateQRCode();
    }

    /**
     * Build the message.
     *
     * @return $this
     */

    public function generateQRCode()
    {
        $url = env("APP_URL").'/admin/actividades/'.$this->inscripcion->idActividad.'/inscripcion/'.$this->inscripcion->idInscripcion.'/persona/'.$this->inscripcion->idPersona; 
        $qr = QrCode::size(200)->generate($url);
        return $qr;
    }
 
    public function build()
    {
        return $this
            ->subject( __('email.inscription_confirmed_title') . ' ' . $this->inscripcion->actividad->nombreActividad)
            ->from('noreplyactividades@techo.org')
            ->view('emails.inscripcionConfirmada')
            ->with([
                'QRCode' => $this->QRCode,
                'inscripcion' => $this->inscripcion,
                'persona' => $this->persona,
            ]);
    }
}
