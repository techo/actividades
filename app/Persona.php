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

    public function inscripciones()
    {
        return $this->hasMany(Inscripcion::class, 'idPersona');
    }
    public function actividades()
    {
        return $this->hasMany(Actividad::class, 'idCoordinador');
    }

    public function getNombreCompletoAttribute() {
    	return $this->nombres . ' ' . $this->apellidoPaterno;
    }

    public function estaInscripto($idActividad) {
        return $this->inscripciones->where('idActividad',$idActividad)->count();
    }

    public function verificacion()
    {
        return $this->hasOne('App\VerificacionMailPersona', 'idPersona');
    }

    public function perfil() {
        $usuario = [
            'id' => $this->idPersona,
            'email' => $this->mail,
            'nombre' => $this->nombres,
            'apellido' => $this->apellidoPaterno,
            'nacimiento' => $this->fechaNacimiento,
            'sexo' => $this->sexo,
            'dni' => $this->dni,
            'pais' => $this->idPais,
            'provincia' => $this->idProvincia,
            'localidad' => $this->idLocalidad,
            'telefono' => $this->telefonoMovil,
            'pass' => ''
        ];
        return $usuario;
    }
}
