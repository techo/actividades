<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Oficina extends Model
{
    protected $table = 'atl_oficinas';
    protected $primaryKey = 'id';

    public function actividades()
    {
        return $this->hasMany(Actividad::class, 'idOficina');
    }
}
