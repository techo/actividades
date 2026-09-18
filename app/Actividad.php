<?php

namespace App;

use App\Concerns\BelongsToCountry;
use App\Http\Resources\MiembroResource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Actividad extends Model
{
    use SoftDeletes;
    use BelongsToCountry; // scope automático por país (columna idPais)
    protected $table = "Actividad";
    protected $primaryKey = "idActividad";
    protected $guarded = ['idActividad'];
    protected $dates =
        [
            'fechaInicio', 'fechaFin',
            'fechaInicioInscripciones', 'fechaFinInscripciones',
            'fechaInicioEvaluaciones', 'fechaFinEvaluaciones',
            'fechaLimitePago',
            'fechaCreacion', 'fechaModificacion',
        ];
    protected $casts = [
        'ficha_medica_campos' => 'array',
        'roles_tags' => 'array',
        'actividades_tags' => 'array',
        'tipo_inscriptos_tag' => 'array',
        'metodos_pago' => 'array',
        'permite_exencion' => 'boolean',
    ];

    const CREATED_AT = 'fechaCreacion';
    const UPDATED_AT = 'fechaModificacion';

    public function tipo()
    {
        return $this->belongsTo(Tipo::class, 'idTipo', 'idTipo');
    }

    public function equipo()
    {
        return $this->belongsTo(Equipo::class, 'idEquipo', 'idEquipo');
    }

    public function comunidades()
    {
        return $this->belongsToMany(Comunidad::class, 'actividad_comunidad', 'idActividad', 'idComunidad' );
    }

    public function inscripciones()
    {
        return $this->hasMany(Inscripcion::class, 'idActividad');
    }

    public function preguntas()
    {
        return $this->hasMany(ActividadPregunta::class, 'actividad_id', 'idActividad')
                    ->with('condiciones')
                    ->orderBy('orden');
    }

    public function getCantidadPresentesAttribute()
    {
        return $this->inscripciones()->where('presente','=',1)->count();
    }

    public function membresias()
    {
       return GrupoRolPersona::where('idActividad', '=', $this->idActividad)->get();
    }

    public function evaluaciones()
    {
        return $this->hasMany(EvaluacionActividad::class, 'idActividad');
    }

    public function evaluacionesVoluntarios()
    {
        return $this->hasMany(EvaluacionPersona::class, 'idActividad');
    }

    /**
     * Todos los grupos de la actividad (hasta el más interno)
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function grupos()
    {
        return $this->hasMany(Grupo::class, 'idActividad')->orderBy('nombre');
    }

    public function jornadas()
    {
        return $this->hasMany(Jornada::class, 'idActividad')->orderBy('nombre');
    }

    public function getGrupoRaizAttribute()
    {
        return Grupo::where('idActividad', $this->idActividad)->where('idPadre', 0)->first();
    }

    public function inscriptos()
    {
       return Persona::whereIn('idPersona', $this->inscripciones->pluck('idPersona'))
           ->orderBy('nombres')
           ->get();
    }

    public function getMiembrosAttribute()
    {
        $grupoRaiz = Grupo::where('idPadre', '=', 0)
            ->where('idActividad','=', $this->idActividad)
            ->first();
        if (!is_null($grupoRaiz)) {
            $personas = Persona::join('Grupo_Persona', 'Persona.idPersona', '=', 'Grupo_Persona.idPersona')
                ->where('Grupo_Persona.idActividad', '=', $this->idActividad)
                ->where('Grupo_Persona.idGrupo', '=', $grupoRaiz->idGrupo)
                ->get();

            foreach ($personas as $persona) {
                $todosArray['arbol'][] = new MiembroResource($persona);
            }

            foreach ($grupoRaiz->grupos as $grupo) {
                $todosArray['arbol'][] = new MiembroResource($grupo);
            }
            $todosArray['idRaiz'] = $grupoRaiz->idGrupo;
            return $todosArray;
        }
        // en las actividades viejas $grupoRaiz = null

        $grupoRaiz = Grupo::create([
                'nombre'    => $this->nombreActividad,
                'idPadre'   => 0,
                'idActividad' => $this->idActividad
                ]);
        $todosArray['arbol'] = [];
        $todosArray['idRaiz'] = $grupoRaiz->idGrupo;
        return $todosArray;
    }

    public function getPromedioEvaluacionesAttribute()
    {
        return EvaluacionActividad::where('idActividad', '=', $this->idActividad)
            ->whereNotNull('puntaje')
            ->get()
            ->avg('puntaje');
    }
    public function inscripciones_validas()
    {
        return $this->inscripciones()->get();
    }

    public function comunidad()
    {
        return $this->inscripciones()
            ->whereHas('persona', function ($query) {
                $query->whereNull('deleted_at'); // Filtra solo personas activas
            })
            ->with('persona:idPersona,nombres,photo,instagram,deleted_at')
            ->get()
            ->filter(function ($inscripcion) {
                return $this->estadoInscripcion($inscripcion->idPersona) === 'confirmed';
            })
            ->map(function ($inscripcion) {
                return $inscripcion->persona;
            })
            ->values();;
    }

    /**
     * ¿Las inscripciones están abiertas? La actividad debe estar 'Abierta'
     * (estadoConstruccion) y dentro del período de inscripción. Una fecha nula
     * significa "sin restricción por ese lado" (null-safe: hay ~2757 actividades
     * legacy con fechas de inscripción nulas). Fuente única usada por la página
     * pública (show), el inicio del flujo (puntoDeEncuentro) y el alta (create).
     */
    public function inscripcionesAbiertas(): bool
    {
        $ahora = \Carbon\Carbon::now();

        return $this->estadoConstruccion === 'Abierta'
            && (is_null($this->fechaInicioInscripciones) || $this->fechaInicioInscripciones->lte($ahora))
            && (is_null($this->fechaFinInscripciones) || $this->fechaFinInscripciones->gte($ahora));
    }

    public function puntosEncuentro()
    {
        return $this->hasMany(PuntoEncuentro::class, 'idActividad')->with('responsable');
    }

    public function unidadOrganizacional()
    {
        return $this->belongsTo(\App\UnidadOrganizacional::class, 'idUnidadOrganizacional', 'idUnidadOrganizacional');
    }

    public function oficina()
    {
        return $this->belongsTo(\App\Oficina::class, 'idOficina', 'id');
    }

    public function modificadoPor()
    {
        return $this->belongsTo(Persona::class, 'idPersonaModificacion', 'idPersona');
    }

    public function coordinadores()
    {
        return $this->hasMany(Coordinador::class, 'idActividad');
        // return $this->belongsTo(Persona::class, 'idCoordinador', 'idPersona');
    }

    public function scopePersonaInscripta($query, $idPersona) {
        return $this->inscripciones()->where('idPersona', $idPersona)->get()->count();
    }

    public function localidad()
    {
        return $this->belongsTo(Localidad::class, 'idLocalidad', 'id')
                    ->withDefault(['localidad' => 'No definida']);
    }

    public function provincia()
    {
        return $this->hasOne(Provincia::class, 'id', 'idProvincia');
    }

    public function pais()
    {
        return $this->hasOne(Pais::class, 'id', 'idPais');
    }

    public function escuelas()
    {
        return $this->hasMany(Escuela::class, 'idActividad');
    }

    public function generarLinkPago()
    {
        return $this->LinkPago . "&numero=" .$this->idActividad;
    }

    public function datosInscriptos($idActividad)
    {

       return Actividad::findOrFail($idActividad)
        ->inscripciones()
        ->select(['idPersona', 'estado'])
        ->get()
        ->toArray();

    }

    /**
     * ¿El plazo de pago ya venció (respecto de HOY)?
     *
     * La fecha límite de pago es INCLUSIVA del día cargado: se puede pagar
     * durante todo ese día y el plazo recién vence al pasar al día siguiente.
     * Aunque `fechaLimitePago` es un dateTime, el backoffice la guarda con hora
     * 00:00 (el input de hora del form no se persiste), así que la comparación
     * es por DÍA, no por hora. Fuente única para tarjetas, show, EstadoInscripcion
     * y el flujo de pago (Stripe/PayU), que antes comparaban cada uno distinto y
     * bloqueaban el día límite adelantado (a las 00:00).
     */
    public function pagoFueraDeFecha(): bool
    {
        if (empty($this->fechaLimitePago)) {
            return false;
        }

        return \Carbon\Carbon::now()->startOfDay()
            ->greaterThan($this->fechaLimitePago->copy()->startOfDay());
    }

    /**
     * ¿Un pago realizado en la fecha $fecha cae DENTRO del plazo?
     *
     * Mismo criterio inclusivo por día que pagoFueraDeFecha(): un pago hecho el
     * propio día límite es válido. Se usa en el flujo PayU, donde se compara
     * contra la fecha real de la transacción (no contra "ahora").
     */
    public function pagoDentroDeFecha(\Carbon\Carbon $fecha): bool
    {
        if (empty($this->fechaLimitePago)) {
            return true;
        }

        return $fecha->copy()->startOfDay()
            ->lessThanOrEqualTo($this->fechaLimitePago->copy()->startOfDay());
    }

    public function estadoInscripcion($idPersona = null)
    {
        if(!$idPersona) return false;

        $inscripcion = $this->inscripciones()->where('idPersona', '=', $idPersona)->first();

        // Fuente única: App\Services\EstadoInscripcion. Vocabulario inglés.
        return \App\Services\EstadoInscripcion::toEnglish(
            \App\Services\EstadoInscripcion::resolve($this, $inscripcion)
        );
    }

    public function setFechaFinInscripcionesAttribute($value)
    {
        $this->attributes['fechaFinInscripciones'] = \Carbon\Carbon::parse($value);
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($actividad) {});

        static::updating(function ($actividad) {
            // Guard: en comandos Artisan/jobs/colas/seeders no hay usuario autenticado.
            // Sin este check, auth()->user()->idPersona tira "property of non-object".
            if (auth()->check()) {
                $actividad->idPersonaModificacion = auth()->user()->idPersona;
            }
            Auditoria::crear($actividad);
        });
    }
}
