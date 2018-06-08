<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GrupoRolPersona extends Model
{
    protected $table = "Grupo_Persona";
    protected $guarded = ['id'];

    public function grupo()
    {
        return $this->belongsTo(Grupo::class, 'idGrupo', 'idGrupo');
    }

    public function persona()
    {
        return $this->belongsTo(Persona::class, 'idPersona', 'idPersona');
    }

    public function actividad()
    {
        return $this->belongsTo(Actividad::class, 'idActividad', 'idActividad');
    }
}
