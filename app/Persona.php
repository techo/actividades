<?php

namespace App;

use App\Mail\ForgotPassword;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Passport\HasApiTokens;

class Persona extends Authenticatable implements MustVerifyEmail
{
    use Notifiable, HasRoles, SoftDeletes, HasApiTokens;
    protected $table = 'Persona';
    protected $primaryKey = 'idPersona';
    protected $hidden = ['password', 'remember_token', 'google_id', 'facebook_id', 'unsubscribe_token'];
    protected $fillable = ['recibirMails', 'recibir_push', 'nombres', 'unsubscribe_token', 'mail', 'password', 'apellidoPaterno', 'fechaNacimiento', 'telefono', 'telefonoMovil', 'genero', 'dni', 'acepta_marketing', 'idPais','idProvincia','idLocalidad', 'idUnidadOrganizacional', 'canal_contacto', 'registro_origen', 'estadoPersona', 'photo', 'instagram', 'primer_acceso_app', 'ultimo_acceso_app'];
    protected $dates = ['deleted_at', 'primer_acceso_app', 'ultimo_acceso_app'];
    protected $appends = array('estado_voluntario');

    protected static function boot()
    {
        parent::boot();

        static::deleted(function ($persona) {
            $persona->integrantes()->delete();
        });
    }

    public function routeNotificationForMail($notification)
    {
        return $this->mail;
    }

    public function sendEmailVerificationNotification()
    {
        \Log::info('Mail de verificación encolado para ' . $this->mail);
        $this->notify((new \App\Notifications\VerifyEmail)->locale(app()->getLocale()));
    }

    public function sendRegistroUsuarioNotification()
    {
        \Log::info('Mail de registro encolado para ' . $this->mail);
        $this->notify((new \App\Notifications\RegistroUsuario)->locale(app()->getLocale()));
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
        return $this->hasMany(Coordinador::class, 'idPersona');
    }
    // equipos relacionaciondos
    public function integrantes()
    {
        return $this->hasMany(Integrante::class, 'idPersona', 'idPersona');
    }

    public function reuniones()
    {
        return $this->belongsToMany(EquipoReunion::class, 'equipo_reunion_persona', 'idPersona', 'idReunion');
    }

    public function actividadesCreadas()
    {
        return $this->hasMany(Actividad::class, 'idPersonaCreacion');
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

    public function evaluacionesActividadRealizadas()
    {
        return $this->hasMany(EvaluacionActividad::class, 'idPersona', 'idPersona');
    }

    public function evaluacionesImpactoActividadRealizadas()
    {
        return $this->hasMany(EvaluacionImpactoActividad::class, 'idPersona', 'idPersona');
    }
    
    public function getPromedioSocialAttribute()
    {
        return $this->evaluacionesRecibidas->avg('puntajeSocial');
    }

    public function getPromedioTecnicoAttribute()
    {
        return $this->evaluacionesRecibidas->avg('puntajeTecnico');
    }

    public function getEstadoVoluntarioAttribute()
    {
        if($this->estadoPersona == "Suspendido")
            return $this->estadoPersona;
        else if ($this->estadoPersona == "Desvinculado")
            return $this->estadoPersona;

        if ($this->estado_persona == "Habilitado" || !$this->estado_persona )
        {
            if ($this->inscripciones()->where('presente',1)->count() > 0)
                return $this->inscripciones()->where('presente',1)->count() . " Presentes";
            else if ($this->inscripciones()->where('presente',0)->count() > 1)
                return "Sin Presentes";
            else
                return "Primera Inscripción";
        }
        return $this->estadoPersona;
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
        return $this->inscripciones->where('idActividad',$idActividad)->count();
    }

    public function estadoInscripcion($idActividad) {
        $inscripcion = $this->inscripciones->where('idActividad',$idActividad)->first();
        $actividad = Actividad::findOrFail($idActividad);

        // Fuente única: App\Services\EstadoInscripcion. Vocabulario español (backoffice).
        return \App\Services\EstadoInscripcion::toSpanish(
            \App\Services\EstadoInscripcion::resolve($actividad, $inscripcion)
        );
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

    /**
     * Multi-país (chokepoint): ids de país que el usuario puede administrar/alcanzar.
     * Prioriza el pivote `persona_paises_permitidos`; si no tiene filas, cae al
     * `idPaisPermitido` único (retrocompatible). Devuelve [] cuando no hay restricción
     * explícita (usar junto con esGlobalPais() para saber si eso significa "todos").
     *
     * @return int[]
     */
    public function paisesPermitidosIds(): array
    {
        $pivote = \DB::table('persona_paises_permitidos')
            ->where('idPersona', $this->idPersona)
            ->pluck('idPais')
            ->map(function ($v) { return (int) $v; })
            ->all();

        if (!empty($pivote)) {
            return array_values(array_unique($pivote));
        }

        $unico = (int) $this->idPaisPermitido;

        return $unico > 0 ? [$unico] : [];
    }

    /**
     * True si el usuario alcanza TODOS los países (admin global): no tiene países
     * explícitos en el pivote y su idPaisPermitido es 0/null. Tener pivote lo acota
     * a esos países (no global), aunque idPaisPermitido esté vacío.
     */
    public function esGlobalPais(): bool
    {
        if (!empty($this->idPaisPermitido)) {
            return false;
        }

        return !\DB::table('persona_paises_permitidos')
            ->where('idPersona', $this->idPersona)
            ->exists();
    }

    public function provincia()
    {
        return $this->hasOne(Provincia::class, 'id', 'idProvincia');
    }

    public function localidad()
    {
        return $this->hasOne(Localidad::class, 'id', 'idLocalidad');
    }

    public function fusionar($target)
    {
        Inscripcion::where('idPersona', $target->idPersona)
            ->update(['idPersona' => $this->idPersona]);

        GrupoRolPersona::where('idPersona', $target->idPersona)
            ->update(['idPersona' => $this->idPersona]);

        Actividad::where('idCoordinador', $target->idPersona)
            ->update(['idCoordinador' => $this->idPersona]);

        // Membresías de coordinador (tabla join `coordinadores`), que alimentan
        // la relación actividades(). Sin esto, la fusión perdía los accesos de
        // coordinador de la cuenta secundaria y dejaba registros huérfanos.
        Coordinador::where('idPersona', $target->idPersona)
            ->update(['idPersona' => $this->idPersona]);

        Actividad::where('idPersonaCreacion', $target->idPersona)
            ->update(['idPersonaCreacion' => $this->idPersona]);

        Actividad::where('idPersonaModificacion', $target->idPersona)
            ->update(['idPersonaModificacion' => $this->idPersona]);

        PuntoEncuentro::where('idPersona', $target->idPersona)
            ->update(['idPersona' => $this->idPersona]);

        EvaluacionActividad::where('idPersona', $target->idPersona)
            ->update(['idPersona' => $this->idPersona]);

        EvaluacionPersona::where('idEvaluado', $target->idPersona)
            ->update(['idEvaluado' => $this->idPersona]);

        EvaluacionPersona::where('idEvaluador', $target->idPersona)
            ->update(['idEvaluador' => $this->idPersona]);

        $target->delete();

    }
    public function fichaMedica()
    {
        return $this->hasOne(FichaMedica::class, 'idPersona', 'idPersona');
    }

    public function estudios()
    {
        return $this->hasMany(Estudios::class, 'idPersona', 'idPersona');
    }

    public function dispositivos()
    {
        return $this->hasMany(Dispositivo::class, 'idPersona', 'idPersona');
    }
}
