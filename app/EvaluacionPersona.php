<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EvaluacionPersona extends Model
{
    protected $table = 'EvaluacionPersona';
    protected $primaryKey = 'idEvaluacionPersona';
    protected $guarded = ['idEvaluacion'];

    public function persona()
    {
        return $this->belongsTo(Persona::class, 'idPersona', 'idEvaluado');
    }

    public function respuestas()
    {
        return $this->hasMany(EvaluacionPersonaRespuesta::class, 'idEvaluacionPersona', 'idEvaluacionPersona');
    }
}
