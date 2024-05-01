<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Provincia extends Model
{
    protected $table = 'atl_provincias';
    protected $primaryKey = 'id';
    public $timestamps = false;

    public function localidades()
    {
        return $this->hasMany(Localidad::class, 'id_provincia', 'id');
    }

    public function pais()
    {
        return $this->belongsTo(Pais::class, 'id_pais', 'id');
    }

    public function oficina()
    {
        return $this->belongsTo(Oficina::class, 'idOficina', 'id');
    }
}
