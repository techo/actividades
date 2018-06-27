<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EvaluacionActividad extends Model
{
    protected $table = 'EvaluacionActividad';
    protected $primaryKey = 'idEvaluacion';
    protected $guarded = ['idEvaluacion'];

    public function actividad()
    {
        return $this->belongsTo(Actividad::class, 'idActividad', 'idActividad');
    }
}
