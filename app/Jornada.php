<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Jornada extends Model
{
    use SoftDeletes;
    protected $table = 'Jornada';
    protected $primaryKey = 'idJornada';
    protected $fillable = ['nombre', 'idActividad', 'idPersona', 'fechaInicio', 'fechaFin', 'activo'];
    protected $guarded = [ 'idJornada' ];

    public function actividad()
    {
        return $this->belongsTo(Actividad::class, 'idActividad', 'idActividad');
    }

    public function responsable()
    {
        return $this->belongsTo(Persona::class, 'idPersona', 'idPersonaResponsable');
    }

    public function inscripciones()
    {
        return $this->belongsToMany(Inscripcion::class, 'InscripcionJornada', 'idJornada', 'idInscripcion');
    }
}
