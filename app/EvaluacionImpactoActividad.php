<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EvaluacionImpactoActividad extends Model
{
    protected $table = 'EvaluacionImpactoActividad';
    protected $primaryKey = 'idEvaluacionImpacto';
    protected $guarded = ['idEvaluacionImpacto'];

    /**
     * Relación con Actividad
     */
    public function actividad()
    {
        return $this->belongsTo(
            Actividad::class,
            'idActividad',
            'idActividad'
        );
    }

    /**
     * Persona que realiza la evaluación
     */
    public function persona()
    {
        return $this->belongsTo(
            Persona::class,
            'idPersona',
            'idPersona'
        );
    }
}
