<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tipo extends Model
{
    protected $table = "Tipo";
    protected $primaryKey = "idTipo";

    public function actividades()
    {
        return $this->hasMany(Actividad::class, 'idActividad');
    }
}
