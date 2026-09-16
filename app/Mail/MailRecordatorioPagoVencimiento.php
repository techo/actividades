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
 * Es transaccional (sale por el mailer por defecto / Gmail), no bulk: es de bajo
 * volumen y alta importancia (la persona pierde el cupo si no paga a tiempo), así
 * que NO debe quedar frenado por mailing.bulk_pausado.
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
            ->from(config('mail.from.address'), __('email.remitente'))
            ->view('emails.recordatorioPagoVencimiento');
    }
}
