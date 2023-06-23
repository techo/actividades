<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EquipoPersonas extends Model
{
    use SoftDeletes;
    protected $table = "equipo_personas";
    protected $primaryKey = "idEquipoPersona";
    protected $fillable = ['idEquipo', 'rol', 'estado', 'idPersona', 'fechaInicio', 'fechaFin'];
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