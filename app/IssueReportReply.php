<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Un mensaje del hilo de un reporte. Ver migración `create_issue_report_replies_table`.
 *
 * - Nota interna de triage (`is_internal = true`) o respuesta visible para quien reportó.
 * - `tipo` = 'mensaje' (escrito a mano) | 'resuelto' (evento del sistema al resolver).
 */
class IssueReportReply extends Model
{
    protected $table = 'issue_report_replies';

    const TIPO_MENSAJE  = 'mensaje';
    const TIPO_RESUELTO = 'resuelto';

    // Voz con la que el equipo firma las respuestas a quien reportó. `author_name` muestra
    // "Techita"; `idPersona` sigue siendo el admin que la escribió (trazabilidad interna).
    const AUTOR_TECHITA = 'Techita';

    protected $fillable = [
        'issue_report_id', 'idPersona', 'author_name',
        'tipo', 'body', 'is_internal', 'notified_at',
    ];

    protected $casts = [
        'issue_report_id' => 'integer',
        'idPersona'       => 'integer',
        'is_internal'     => 'boolean',
        'notified_at'     => 'datetime',
    ];

    public function reporte()
    {
        return $this->belongsTo(IssueReport::class, 'issue_report_id', 'id');
    }

    public function esDeTechita()
    {
        return $this->author_name === self::AUTOR_TECHITA;
    }

    public function autor()
    {
        return $this->belongsTo(Persona::class, 'idPersona', 'idPersona');
    }
}
