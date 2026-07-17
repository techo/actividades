<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Columna personalizada de seguimiento de un listado, compartida por el equipo.
 * Ámbito: (list_key, context_id). Tipo y opciones son inmutables post-creación.
 */
class ListadoColumna extends Model
{
    use SoftDeletes;

    const TIPOS = ['casilla', 'estado', 'etiquetas', 'texto', 'fecha', 'persona'];

    protected $table = 'listado_columnas';

    protected $fillable = [
        'list_key',
        'context_id',
        'nombre',
        'tipo',
        'opciones',
        'orden',
        'created_by',
    ];

    protected $casts = [
        'opciones' => 'array',
    ];

    public function valores()
    {
        return $this->hasMany(ListadoColumnaValor::class, 'columna_id');
    }
}
