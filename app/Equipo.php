<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Equipo extends Model
{
    use SoftDeletes;
    protected $table = "Equipo";
    protected $primaryKey = "idEquipo";
    protected $fillable = ['idOficina', 'nombre', 'idPais', 'activo', 'fechaInicio', 'fechaFin'];
    protected $dates =
        [
            'fechaInicio', 'fechaFin'
        ];

    public function oficina()
    {
        return $this->belongsTo(Oficina::class, 'idOficina', 'idOficina');
    }

    public function pais()
    {
        return $this->hasOne(Pais::class, 'idPais', 'idPais');
    }
    
    public function equipoPersonas()
    {
        return $this->hasMany(EquipoPersonas::class, 'idEquipo');
    }


}
