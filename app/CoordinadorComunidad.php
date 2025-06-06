<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CoordinadorComunidad extends Model
{
    protected $table = 'coordinadores_comunidad';
    protected $primaryKey = 'idCoordinadorComunidad';
    public $timestamps = false;
    protected $guarded = [ 'idCoordinadorComunidad' ];

    public function comunidad()
    {
        return $this->belongsTo(Comunidad::class, 'idComunidad', 'idComunidad');
    }

    public function persona()
    {
        return $this->belongsTo(Persona::class, 'idPersona', 'idPersona');
    }
}
