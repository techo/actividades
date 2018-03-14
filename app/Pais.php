<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Pais extends Model
{
    protected $table = 'atl_pais';
    protected $primaryKey = 'id';

    public function provincias()
    {
        return $this->hasMany(Provincia::class, 'id_pais', 'id');
    }
}
