<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RolIntegrante extends Model
{
    protected $table = 'roles_integrante';
    protected $fillable = ['clave', 'nombre', 'activo'];
}
