<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EquipoReunion extends Model
{
    use SoftDeletes;
    protected $table = "equipo_reunion";
    protected $primaryKey = "idReunion";
    protected $fillable = [
        'idEquipo', 
        'nombre', 
        'fecha', 
        'despliegue', 
        'descripcion', 
        'compromisos', 
        'tipo_reunion'
    ];

    protected $dates =
        [
            'fecha'
        ];


    public function equipo()
    {
        return $this->belongsTo(Equipo::class, 'idEquipo', 'idEquipo');
    }

    public function personas()
    {
        return $this->belongsToMany(Persona::class, 'equipo_reunion_persona', 'idReunion', 'idPersona');
    }
}