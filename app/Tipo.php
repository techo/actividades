<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tipo extends Model
{
    protected $table = "Tipo";
    protected $primaryKey = "idTipo";
    public $timestamps = false;

    public function actividades()
    {
        return $this->hasMany(Actividad::class, 'idActividad');
    }

    public function categoria()
    {
        return $this->belongsTo(CategoriaActividad::class, 'idCategoria', 'id');
    }
}
