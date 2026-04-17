<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Oficina extends Model
{
    protected $table = 'atl_oficinas';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = ['nombre', 'id_pais'];

    public function actividades()
    {
        return $this->hasMany(Actividad::class, 'idOficina');
    }

    public function pais()
    {
        return $this->belongsTo(Pais::class, 'id_pais');
    }

    public function equipos()
    {
        return $this->hasMany(Equipo::class, 'idOficina');
    }

    public function campaigns()
    {
        return $this->hasMany(Campaign::class, 'oficina_id');
    }
}
