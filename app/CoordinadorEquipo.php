<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CoordinadorEquipo extends Model
{
    protected $table = 'coordinadores_equipos';
    protected $primaryKey = 'idCoordinadorEquipo';
    public $timestamps = false;
    protected $guarded = [ 'idCoordinadorEquipo' ];

    public function equipo()
    {
        return $this->belongsTo(Equipo::class, 'idEquipo', 'idEquipo');
    }

    public function persona()
    {
        return $this->belongsTo(Persona::class, 'idPersona', 'idPersona');
    }

}
