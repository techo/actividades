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

    /**
     * ¿El `mail` es una dirección válida y enviable?
     *
     * En la base conviven personas cuyo `mail` NO es un email: altas legacy con
     * el campo vacío o con un nombre/slug sin `@`, y cuentas anonimizadas por la
     * baja de cuenta (ver UsuarioController::delete), que pisan `mail` con un
     * token `str_random(40)`. Enviarles correo tira `Swift_RfcComplianceException`
     * y rompe el job. Esta es la fuente única de "¿se le puede mandar mail?".
     */
    public function tieneMailValido()
    {
        return filter_var($this->mail, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Personas a las que SÍ se les puede enviar mail: aceptan notificaciones y
     * tienen una dirección con forma de email. El chequeo fino (RFC) lo hace
     * tieneMailValido() por fila; en SQL aproximamos con `LIKE '%@%'` para poder
     * filtrar en queries de envío masivo sin traer filas de más.
     */
    public function scopeMailable($query)
    {
        return $query->where('recibirMails', 1)
                     ->whereNotNull('mail')
                     ->where('mail', 'like', '%@%');
    }

    public function routeNotificationForMail($notification)
    {
        // Devolver null hace que Notifiable saltee el canal mail (no intenta
        // enviar) en vez de explotar con una dirección inválida.
        return $this->tieneMailValido() ? $this->mail : null;
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
     * ¿El usuario autenticado puede gestionar a ESTA persona, aunque sea de otro país?
     *
     * Política híbrida para rescatar registros "varados" (los que agarraron el país
     * por defecto y quedan fuera del alcance de su coordinación). Puede gestionarla si:
     *  - es admin global (alcanza todos los países), o
     *  - esta persona es de alguno de sus países permitidos, o
     *  - la persona quedó "sin dueño": su país es el genérico por defecto
     *    (config('app.pais_default')) o un país sin coordinación (no habilitado).
     * Si pertenece a otro país habilitado (otra coordinación real), NO puede: la
     * gestiona esa coordinación o un admin global. Se usa en /admin/usuarios para el
     * rescate por email exacto (ver UsuariosSearch y backoffice\UsuariosController).
     */
    public function gestionableCrossPais(): bool
    {
        $auth = auth()->user();
        if (!$auth) {
            return false;
        }

        if ($auth->esGlobalPais()) {
            return true;
        }

        if (in_array((int) $this->idPais, $auth->paisesPermitidosIds(), true)) {
            return true;
        }

        if ((int) $this->idPais === (int) config('app.pais_default')) {
            return true;
        }

        return empty(optional($this->pais)->habilitado);
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

    /**
     * ¿Podemos NO mandarle el mail porque le va a llegar el push? Sirve para no
     * duplicar el mismo aviso en dos canales y bajar el volumen de envíos de mail
     * (ver migración a SES: el relay de Gmail se satura con las ráfagas).
     *
     * Requiere las tres cosas juntas:
     *  - push activado (recibir_push),
     *  - al menos un dispositivo activo (mismo criterio que PushNotificationService::enviar),
     *  - acceso reciente a la app: sin esto suprimiríamos el mail de quien desinstaló
     *    sin desloguear (el device sigue 'activo' pero el push nunca llega).
     *
     * Es fail-safe: ante la duda (push off, sin device, o sin acceso reciente) devuelve
     * false y el mail se manda igual. Nadie se queda sin el aviso.
     *
     * @param int $diasRecencia ventana de "acceso reciente"; más chico = más conservador.
     */
    public function tienePushConfiable(int $diasRecencia = 60): bool
    {
        if (!$this->recibir_push) {
            return false;
        }

        if (!$this->ultimo_acceso_app || $this->ultimo_acceso_app->lt(now()->subDays($diasRecencia))) {
            return false;
        }

        return $this->dispositivos()->where('activo', true)->exists();
    }
}
