<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Reporte de problema / sugerencia enviado desde el widget "Reportar un problema".
 *
 * Tabla nueva (snake_case, PK `id`). Ver migración
 * `create_issue_reports_table` para el detalle del diseño "agent-ready".
 *
 * Las relaciones a Persona usan la PK legacy `idPersona` explícitamente. El snapshot
 * (`reporter_name`, `reporter_email`, `reporter_role`) se conserva aparte porque un
 * reporte es un hecho histórico y debe seguir siendo legible aunque la persona cambie
 * o se borre.
 */
class IssueReport extends Model
{
    use SoftDeletes;

    protected $table = 'issue_reports';

    // Tipos
    const TYPE_BUG        = 'bug';
    const TYPE_SUGGESTION = 'suggestion';

    // Estados (ciclo de triage)
    const STATUS_NUEVO       = 'nuevo';
    const STATUS_TRIAGE      = 'triage';
    const STATUS_EN_PROGRESO = 'en_progreso';
    const STATUS_RESUELTO    = 'resuelto';
    const STATUS_DESCARTADO  = 'descartado';

    const SEVERITIES = ['low', 'medium', 'high', 'critical'];
    const STATUSES   = [
        self::STATUS_NUEVO,
        self::STATUS_TRIAGE,
        self::STATUS_EN_PROGRESO,
        self::STATUS_RESUELTO,
        self::STATUS_DESCARTADO,
    ];

    protected $fillable = [
        'type', 'status', 'severity', 'area', 'description',
        'idPersona', 'reporter_name', 'reporter_email', 'reporter_role', 'idPais',
        'url', 'route_name', 'os', 'browser', 'screen_resolution', 'viewport',
        'locale', 'release', 'user_agent',
        'console_errors', 'breadcrumbs', 'context',
        'screenshot_path', 'sentry_event_id', 'github_issue_url',
        'assigned_to', 'resolved_at',
    ];

    protected $casts = [
        'idPersona'      => 'integer',
        'idPais'         => 'integer',
        'assigned_to'    => 'integer',
        'console_errors' => 'array',
        'breadcrumbs'    => 'array',
        'context'        => 'array',
        'resolved_at'    => 'datetime',
    ];

    public function reportadoPor()
    {
        return $this->belongsTo(Persona::class, 'idPersona', 'idPersona');
    }

    public function asignadoA()
    {
        return $this->belongsTo(Persona::class, 'assigned_to', 'idPersona');
    }

    public function pais()
    {
        return $this->belongsTo(Pais::class, 'idPais', 'id');
    }

    public function scopeAbiertos($query)
    {
        return $query->whereNotIn('status', [self::STATUS_RESUELTO, self::STATUS_DESCARTADO]);
    }
}
