<?php

namespace App\Mail;

use App\Mail\Concerns\HasMailLocale;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Recordatorio PROACTIVO: se envía 1-2 días ANTES de que venza la fecha límite
 * de pago, a inscripciones que están en estado "confirmar pagando" (todavía a
 * tiempo de aportar). Reemplaza en utilidad al viejo aviso de "venció el plazo"
 * (que solo llegaba cuando ya no se podía hacer nada).
 *
 * Es transaccional en importancia (la persona pierde el cupo si no paga a tiempo),
 * así que NO debe quedar frenado por mailing.bulk_pausado; pero sale por SES
 * (EnviarMailTransaccionalSes) porque en una actividad grande es un envío masivo.
 * Por eso el from es la identidad verificada en SES (mailing.from_bulk).
 */
class MailRecordatorioPagoVencimiento extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels, HasMailLocale;

    public $mailLocale;
    public $inscripcion;
    public $persona;
    public $actividad;

    public function __construct($inscripcion)
    {
        $this->inscripcion = $inscripcion;
        $this->persona = $inscripcion->persona;
        $this->actividad = $inscripcion->actividad;
        $this->mailLocale = optional($inscripcion->persona->pais)->locale ?? config('app.locale');
    }

    public function build()
    {
        return $this
            ->subject(__('email.payment_reminder_title') . ' ' . $this->inscripcion->actividad->nombreActividad)
            ->from(config('mailing.from_bulk'), __('email.remitente'))
            ->view('emails.recordatorioPagoVencimiento');
    }
}
