<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PuntoEncuentro extends Model
{
    protected $table = 'PuntoEncuentro';
    protected $primaryKey = 'idPuntoEncuentro';
    public $timestamps = false;

    public function actividad()
    {
        return $this->belongsTo(Actividad::class, 'idActividad', 'idActividad');
    }

    public function responsable()
    {
        return $this->belongsTo(Persona::class, 'idPersona', 'idPersona');
    }

    public function pais()
    {
        return $this->belongsTo( Pais::class, 'idPais', 'id');
    }

    public function provincia()
    {
        return $this->belongsTo( Provincia::class, 'idProvincia', 'id');
    }

    public function localidad()
    {
        return $this->belongsTo(Localidad::class, 'idLocalidad', 'id');
    }

    public function inscripciones()
    {
        return $this->hasMany(Inscripcion::class, 'idPuntoEncuentro', 'idPuntoEncuentro');
    }

    public function tieneInscriptos()
    {
        if($this->inscripciones()->where('estado', '!=', 'Desinscripto')->count() > 0) {
            return true;
        }
        return false;
    }
}
