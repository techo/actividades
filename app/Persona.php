<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Persona extends Authenticatable
{
    use Notifiable;
    protected $table = 'Persona';
    protected $primaryKey = 'idPersona';

    public function puntosEncuentro()
    {
        return $this->hasMany(PuntoEncuentro::class, 'idPersona');
    }

    public function inscripcion()
    {
        return $this->hasMany(Inscripcion::class, 'idPersona');
    }

    public function getNombreCompletoAttribute() {
    	return $this->nombres . ' ' . $this->apellidoPaterno;
    }

    public function estaInscripto($idActividad) {
        return $this->inscripcion->where('idActividad',$idActividad)->count();
    }

    public function verificacion()
    {
        return $this->hasOne('App\VerificacionMailPersona', 'idPersona');
    }
}
