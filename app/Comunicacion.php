<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Una comunicación saliente disparada desde el hub (pantalla "Invitaciones").
 *
 * Genérica por diseño: `canal` (push/email/whatsapp) y `objetivo_tipo`/`objetivo_id`
 * (actividad/campaign) permiten crecer sin cambiar el esquema. El detalle por persona
 * está en Comunicacion::destinatarios(), base para la atribución de inscripciones.
 */
class Comunicacion extends Model
{
    protected $table = 'comunicaciones';

    protected $fillable = [
        'canal',
        'objetivo_tipo',
        'objetivo_id',
        'segmento',
        'paises',
        'titulo',
        'mensaje',
        'destinatarios_count',
        'idAdmin',
        'estado',
    ];

    protected $casts = [
        'paises'              => 'array',
        'destinatarios_count' => 'integer',
    ];

    const CANAL_PUSH     = 'push';
    const CANAL_EMAIL    = 'email';
    const CANAL_WHATSAPP = 'whatsapp';

    const OBJETIVO_ACTIVIDAD = 'actividad';
    const OBJETIVO_CAMPANA   = 'campaign';

    const ESTADO_PROCESANDO = 'procesando';
    const ESTADO_ENVIADA    = 'enviada';
    const ESTADO_ERROR      = 'error';

    /** Detalle por persona: a quiénes se despachó esta comunicación. */
    public function destinatarios()
    {
        return $this->hasMany(ComunicacionDestinatario::class, 'comunicacion_id');
    }

    /** Admin que disparó el envío (trazabilidad). */
    public function admin()
    {
        return $this->belongsTo(Persona::class, 'idAdmin', 'idPersona');
    }

    /**
     * Resuelve el objetivo concreto (Actividad o Campaign) según objetivo_tipo.
     * No es una relación Eloquent porque el objetivo es polimórfico por string.
     */
    public function objetivo()
    {
        if ($this->objetivo_tipo === self::OBJETIVO_CAMPANA) {
            return Campaign::find($this->objetivo_id);
        }

        return Actividad::todosLosPaises()->find($this->objetivo_id);
    }
}
