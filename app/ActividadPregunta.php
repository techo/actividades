<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ActividadPregunta extends Model
{
    protected $table = 'actividad_preguntas';
    protected $primaryKey = 'id';

    protected $fillable = [
        'actividad_id',
        'pregunta',
        'descripcion',
        'tipo',
        'opciones',
        'requerida',
        'orden',
    ];

    protected $casts = [
        'opciones'  => 'array',
        'requerida' => 'boolean',
    ];

    public function actividad()
    {
        return $this->belongsTo(Actividad::class, 'actividad_id', 'idActividad');
    }
}
