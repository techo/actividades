<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Preferencia de columnas visibles de un listado, por usuario y contexto.
 * Clave lógica: (persona_id, list_key, context_id).
 */
class ListadoPreferencia extends Model
{
    protected $table = 'listado_preferencias';

    protected $fillable = [
        'persona_id',
        'list_key',
        'context_id',
        'columnas',
    ];

    protected $casts = [
        'columnas' => 'array',
    ];

    public function persona()
    {
        return $this->belongsTo(Persona::class, 'persona_id', 'idPersona');
    }
}
