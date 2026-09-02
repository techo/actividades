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
        'estado',
    ];

    const ESTADO_ENVIADO = 'enviado';
    const ESTADO_ERROR   = 'error';

    public function comunicacion()
    {
        return $this->belongsTo(Comunicacion::class, 'comunicacion_id');
    }

    public function persona()
    {
        return $this->belongsTo(Persona::class, 'idPersona', 'idPersona');
    }
}
