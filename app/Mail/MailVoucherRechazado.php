<?php

namespace App\Mail;

use App\Mail\Concerns\HasMailLocale;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class MailVoucherRechazado extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels, HasMailLocale;

    public $mailLocale;
    public $inscripcion;
    public $persona;
    public $actividad;
    public $motivo;

    public function __construct($inscripcion, $motivo = null)
    {
        $this->inscripcion = $inscripcion;
        $this->persona     = $inscripcion->persona;
        $this->actividad   = $inscripcion->actividad;
        $this->motivo      = $motivo;
        $this->mailLocale  = optional($inscripcion->persona->pais)->locale ?? config('app.locale');
    }

    public function build()
    {
        return $this
            ->subject(__('email.voucher_rechazado_subject') . ' ' . $this->inscripcion->actividad->nombreActividad)
            ->from('noreplyactividades@techo.org')
            ->view('emails.voucherRechazado');
    }
}
