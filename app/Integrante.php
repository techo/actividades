<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Integrante extends Model
{
    use SoftDeletes;
    protected $table = "Integrantes";
    protected $primaryKey = "idIntegrante";
    protected $fillable = ['idEquipo', 'rol', 'estado', 'despliegue', 'relacion', 'idPersona', 'fechaInicio', 'fechaFin'];
    protected $dates =
        [
            'fechaInicio', 'fechaFin'
        ];


    public function equipo()
    {
        return $this->belongsTo(Equipo::class, 'idEquipo', 'idEquipo');
    }

    public function persona()
    {
        return $this->hasOne(Persona::class, 'idPersona', 'idPersona');
    }

}