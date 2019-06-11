<?php

namespace App;

use App\Mail\ForgotPassword;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Traits\HasRoles;

class Persona extends Authenticatable
{
    use Notifiable, HasRoles;
    protected $table = 'Persona';
    protected $primaryKey = 'idPersona';
    protected $hidden = ['password', 'remember_token'];
    protected $fillable = ['recibirMails', 'nombres', 'unsubscribe_token', 'mail'];

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

    public function gruposRoles()
    {
        return $this->hasMany(GrupoRolPersona::class, 'idPersona', 'idPersona');
    }

    public function evaluacionesRecibidas()
    {
        return $this->hasMany(EvaluacionPersona::class, 'idEvaluado', 'idPersona');
    }

    public function evaluacionesRealizadas()
    {
        return $this->hasMany(EvaluacionPersona::class, 'idEvaluador', 'idPersona');
    }

    public function getPromedioSocialAttribute()
    {
        return $this->evaluacionesRecibidas->avg('puntajeSocial');
    }

    public function getPromedioTecnicoAttribute()
    {
        return $this->evaluacionesRecibidas->avg('puntajeTecnico');
    }

    public function getNombreCompletoAttribute() {
        return $this->nombres . ' ' . $this->apellidoPaterno;
    }

    public function grupoAsignadoEnActividad($idActividad)
    {
        return $this->gruposRoles()->where('idActividad', $idActividad)->first();
    }

    public function estaInscripto($idActividad) {
        return $this->inscripciones->where('idActividad',$idActividad)->count();
    }

    public function estaPreInscripto($idActividad) {
        return $this->inscripciones->where('idActividad',$idActividad)->where('estado','Pre-Inscripto')->count();
    }

    public function noEstaInscripto($idActividad) {
        return $this->inscripciones->where('idActividad',$idActividad)->first();
    }

    public function verificacion()
    {
        return $this->hasOne('App\VerificacionMailPersona', 'idPersona');
    }

    public function sendPasswordResetNotification($token)
    {
        Mail::to($this->mail)->send(new ForgotPassword($token, $this));
    }

    public function getEmailForPasswordReset()
    {
        return $this->mail;
    }

    public function inscripcionActividad($idActividad)
    {
        return Inscripcion::where('idActividad', $idActividad)
            ->where('idPersona', auth()->user()->idPersona)
            ->first();
    }

    public function pais()
    {
        return $this->hasOne(Pais::class, 'id', 'idPais');
    }

    public function provincia()
    {
        return $this->hasOne(Provincia::class, 'id', 'idProvincia');
    }

    public function localidad()
    {
        return $this->hasOne(Localidad::class, 'id', 'idLocalidad');
    }
}
