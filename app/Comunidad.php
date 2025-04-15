<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comunidad extends Model
{
    use SoftDeletes;
    protected $table = "Comunidad";
    protected $primaryKey = "idComunidad";
    protected $fillable = ['idOficina', 'nombre', 'idLocalidad','idProvincia', 'activo', 'idPais'];

    public function oficina()
    {
        return $this->belongsTo(Oficina::class, 'idOficina', 'id');
    }

    public function localidad()
    {
        return $this->hasOne(Localidad::class, 'idLocalidad', 'id');
    }

    public function pais()
    {
        return $this->hasOne(Pais::class, 'idPais', 'id');
    }

    public function actividades()
    {
        return $this->belongsToMany(Actividad::class, 'actividad_comunidad', 'idComunidad', 'idActividad');
    }

    public function equipos()
    {
        return $this->belongsToMany(Equipo::class, 'actividad_equipo', 'idComunidad', 'idEquipo');
    }
    
}
