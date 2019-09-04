<?php

namespace App;

use App\Mail\ForgotPassword;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Contracts\Auth\MustVerifyEmail;

class Persona extends Authenticatable implements MustVerifyEmail
{
    use Notifiable, HasRoles;
    protected $table = 'Persona';
    protected $primaryKey = 'idPersona';
    protected $hidden = ['password', 'remember_token'];
    protected $fillable = ['recibirMails', 'nombres', 'unsubscribe_token', 'mail'];

    public function routeNotificationForMail($notification)
    {
        return $this->mail;
    }

    public function sendEmailVerificationNotification()
    {
        $this->notify(new \App\Notifications\VerifyEmail);
    }

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
        return $this->inscripciones()->where('idActividad', $idActividad)->first()->rol;
    }

    public function estaInscripto($idActividad) {
        return $this->inscripciones->where('idActividad',$idActividad)->count();
    }

    public function estaPreInscripto($idActividad) {
        return $this->inscripciones->where('idActividad',$idActividad)->count();
    }

    public function estadoInscripcion($idActividad) {
        $inscripcion = $this->inscripciones->where('idActividad',$idActividad)->first();
        $actividad = Actividad::findOrFail($idActividad);

        if(!$inscripcion) { 
            return false;
        }

        if($actividad->confirmacion == 1 && $actividad->pago == 1) {
            if ($inscripcion->confirma && $inscripcion->pago) return 'CONFIRMADO';
            elseif ($inscripcion->confirma == 0) return 'ESPERAR CONFIRMACIÓN';
            else {
                if(!$actividad->fechaLimitePago || 
                    $actividad->fechaLimitePago && $actividad->fechaLimitePago->greaterThan(Carbon::now()))
                    return 'CONFIRMAR PARTICIPACIÓN';
                else
                    return 'FECHA DE CONFIRMACIÓN VENCIDA';
            }
        }

        if($actividad->confirmacion == 1) {
            if ($inscripcion->confirma) return 'CONFIRMADO';
            else return 'ESPERAR CONFIRMACIÓN';
        }

        if($actividad->pago == 1) {
            if ($inscripcion->pago) return 'CONFIRMADO';
            else {
                if(!$actividad->fechaLimitePago || 
                    $actividad->fechaLimitePago && $actividad->fechaLimitePago->greaterThan(Carbon::now()))
                    return 'CONFIRMAR PARTICIPACIÓN';
                else
                    return 'FECHA DE CONFIRMACIÓN VENCIDA';
            }
        }

        if($actividad->confirmacion == 0 && $actividad->pago == 0) {
            return "CONFIRMADO";
        }

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
