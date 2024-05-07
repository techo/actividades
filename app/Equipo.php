<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Equipo extends Model
{
    use SoftDeletes;
    protected $table = "Equipo";
    protected $primaryKey = "idEquipo";
    protected $fillable = ['idOficina', 'nombre', 'idPais', 'activo', 'fechaInicio', 'fechaFin', 'idEquipoPadre', 'area'];
    protected $dates =
        [
            'fechaInicio', 'fechaFin'
        ];

    public function oficina()
    {
        return $this->belongsTo(Oficina::class, 'idOficina', 'id');
    }

    public function pais()
    {
        return $this->hasOne(Pais::class, 'idPais', 'id');
    }
    
    public function integrantes()
    {
        return $this->hasMany(Integrante::class, 'idEquipo');
    }

    public function coordinadores()
    {
        return $this->hasMany(CoordinadorEquipo::class, 'idEquipo');
    }

    public function subEquipos()
    {
        return $this->hasMany(Equipo::class, 'idEquipoPadre', 'idEquipo');
    }

    public function equipoPadre()
    {
        return $this->belongsTo(Equipo::class, 'idEquipo', 'idEquipoPadre');
    }

}
