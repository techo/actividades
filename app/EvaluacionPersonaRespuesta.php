<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EvaluacionPersonaRespuesta extends Model
{
    protected $table = 'evaluacion_persona_respuestas';
    protected $primaryKey = 'idEvaluacionPersonaRespuesta';
    protected $fillable = ['idEvaluacionPersona','question_key','score','comentario', 'tags_positivos', 'tags_negativos'];

    protected $casts = [
        'tags_positivos' => 'array',
        'tags_negativos' => 'array',
    ];

    public function evaluacion()
    {
        return $this->belongsTo(EvaluacionPersona::class, 'idEvaluacionPersona', 'idEvaluacionPersona');
    }
}