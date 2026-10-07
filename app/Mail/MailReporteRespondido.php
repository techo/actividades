<?php

namespace App\Mail;

use App\IssueReport;
use App\IssueReportReply;
use App\Mail\Concerns\HasMailLocale;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Aviso a quien reportó un problema de que hubo novedades sobre su reporte.
 *
 * Cierra el loop 1:1 de la bandeja de triage. Dos variantes, según la respuesta que lo dispara:
 *   - tipo 'mensaje'  → el equipo le respondió algo puntual.
 *   - tipo 'resuelto' → el reporte se marcó como resuelto (auto-aviso).
 *
 * Transaccional (uno por respuesta); respeta el locale del país de quien reportó.
 */
class MailReporteRespondido extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels, HasMailLocale;

    public $mailLocale;
    public $report;
    public $reply;
    public $esResuelto;

    public function __construct(IssueReport $report, IssueReportReply $reply)
    {
        $this->report     = $report;
        $this->reply      = $reply;
        $this->esResuelto = $reply->tipo === IssueReportReply::TIPO_RESUELTO;
        $this->mailLocale = $report->localeNotificacion();
    }

    public function build()
    {
        $subjectKey = $this->esResuelto
            ? 'email.reporte_resuelto_subject'
            : 'email.reporte_respondido_subject';

        // Sale por Amazon SES (ver App\Jobs\EnviarMailTransaccionalSes): el from DEBE ser
        // una identidad verificada en SES, no la de Gmail del transaccional.
        return $this
            ->subject(__($subjectKey) . ' #' . $this->report->id)
            // Firmado por Techita: cambia solo el NOMBRE del remitente (la dirección sigue
            // siendo la identidad verificada en SES).
            ->from(config('mailing.from_bulk'), $this->reply->esDeTechita()
                ? __('email.reporte_remitente_techita')
                : __('email.remitente'))
            ->view('emails.reporteRespondido');
    }
}
