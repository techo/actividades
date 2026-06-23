<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    protected $table = 'areas';
    protected $fillable = ['clave', 'nombre', 'activo'];

    public function equipos()
    {
        return $this->hasMany(Equipo::class, 'area_id');
    }
}
