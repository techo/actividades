<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Integrante extends Model
{
    use SoftDeletes;
    protected $table = "Integrantes";
    protected $primaryKey = "idIntegrante";
    protected $fillable = [
        'idEquipo', 
        'rol', 
        'estado', 
        'despliegue', 
        'relacion', 
        'idPersona', 
        'fechaInicio', 
        'fechaFin',
        'archivo_carta_compromiso',
        'archivo_plan_de_trabajo',
        'descripcion_rol',
        'meta',
        'hitos',
        'dia_hora_reunion',
        'periodicidad_reunion',
        'impacto',
        'capacidades'
    ];

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