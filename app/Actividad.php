<?php

namespace App;

use App\Http\Resources\MiembroResource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Actividad extends Model
{
    use SoftDeletes;
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
        'tipo_inscriptos_tag' => 'array'
    ];

    const CREATED_AT = 'fechaCreacion';
    const UPDATED_AT = 'fechaModificacion';

    public function tipo()
    {
        return $this->belongsTo(Tipo::class, 'idTipo', 'idTipo');
    }

    public function inscripciones()
    {
        return $this->hasMany(Inscripcion::class, 'idActividad');
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

    public function estadoInscripcion($idPersona = null)
    {
        if(!$idPersona) return false;

        $inscripcion = $this->inscripciones()->where('idPersona', '=', $idPersona)->first();

        if(!$inscripcion) return false;

        if($this->confirmacion == $inscripcion->confirma && $this->pago == $inscripcion->pago) {
            return "confirmed";
        }

        if($this->confirmacion == $inscripcion->confirma && $this->pago != $inscripcion->pago) {
            if( !$this->fechaLimitePago || $this->fechaLimitePago && $this->fechaLimitePago > \Carbon\Carbon::now() )
                return "confirm_by_paying";
            else 
                return "confirmation_date_is_closed";
        }

        if($this->confirmacion != $inscripcion->confirma) {
            return "waiting_for_confirmation";
        }

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
            $actividad->idPersonaModificacion = auth()->user()->idPersona;
            Auditoria::crear($actividad); 
        });
    }
}
