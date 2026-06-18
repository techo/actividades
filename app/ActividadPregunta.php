<?php

namespace App;

use App\Concerns\Preguntable;
use Illuminate\Database\Eloquent\Model;

class ActividadPregunta extends Model
{
    use Preguntable;

    protected $table = 'actividad_preguntas';
    protected $primaryKey = 'id';

    protected $fillable = [
        'actividad_id',
        // El resto de los campos comunes (pregunta, descripcion, tipo, opciones,
        // requerida, orden) los agrega el trait Preguntable.
    ];

    public function actividad()
    {
        return $this->belongsTo(Actividad::class, 'actividad_id', 'idActividad');
    }
}
