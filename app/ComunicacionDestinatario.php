<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Fila por persona de una comunicación: "a esta persona se le despachó esta
 * comunicación". Insumo de la atribución (cruce con inscripciones posteriores).
 */
class ComunicacionDestinatario extends Model
{
    protected $table = 'comunicacion_destinatarios';

    protected $fillable = [
        'comunicacion_id',
        'idPersona',
        'suscripcion_id',
        'estado',
    ];

    const ESTADO_ENVIADO = 'enviado';
    const ESTADO_ERROR   = 'error';

    public function comunicacion()
    {
        return $this->belongsTo(Comunicacion::class, 'comunicacion_id');
    }

    /** Destinatario voluntario (con cuenta). Null si es un suscripto/lead de campaña. */
    public function persona()
    {
        return $this->belongsTo(Persona::class, 'idPersona', 'idPersona');
    }

    /** Destinatario suscripto/lead de campaña (sin cuenta). Null si es una Persona. */
    public function suscripcion()
    {
        return $this->belongsTo(Suscribe::class, 'suscripcion_id');
    }
}
