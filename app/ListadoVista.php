<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Vista guardada de un listado configurable (filtros + agrupación + columnas)
 * por usuario y contexto. Las predefinidas no se persisten: se definen en
 * código (CatalogoListado::defaultViews) y se combinan al leer.
 */
class ListadoVista extends Model
{
    use SoftDeletes;

    protected $table = 'listado_vistas';

    protected $fillable = [
        'persona_id',
        'list_key',
        'context_id',
        'nombre',
        'color',
        'config',
        'orden',
    ];

    protected $casts = [
        'config' => 'array',
    ];

    protected $dates = ['deleted_at'];

    public function persona()
    {
        return $this->belongsTo(Persona::class, 'persona_id', 'idPersona');
    }
}
