<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CategoriaActividad extends Model
{
    protected $table = 'atl_CategoriaActividad';
    protected $primaryKey = 'id';
    public $timestamps = false;

    public function tipos()
    {
        return $this->hasMany(Tipo::class, 'idCategoria', 'id');
    }
}
