<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InscripcionRespuesta extends Model
{
    protected $table = 'inscripcion_respuestas';
    protected $primaryKey = 'id';

    protected $fillable = [
        'inscripcion_id',
        'pregunta_id',
        'respuesta',
    ];

    public function inscripcion()
    {
        return $this->belongsTo(Inscripcion::class, 'inscripcion_id', 'idInscripcion');
    }

    public function pregunta()
    {
        return $this->belongsTo(ActividadPregunta::class, 'pregunta_id', 'id');
    }
}
