<?php

namespace App;

use App\Http\Resources\MiembroResource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Actividad extends Model
{
    protected $table = "Actividad";
    protected $primaryKey = "idActividad";
    protected $guarded = ['idActividad', 'pDNI'];
    protected $fillable = ['nombreActividad', 'fechaInicio'];
    protected $dates =
        [
            'fechaCreacion', 'fechaModificacion',
            'fechaInicio', 'fechaFin',
            'fechaInicioInscripciones', 'fechaFinInscripciones',
            'fechaInicioEvaluaciones', 'fechaFinEvaluaciones'

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
        return $this->inscripciones()->whereNotIn('estado',['Desinscripto'])->get();
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

    public function coordinador()
    {
        return $this->belongsTo(Persona::class, 'idCoordinador', 'idPersona');
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
       $query = Actividad::newQuery();

       $query->join('Inscripcion', 'Inscripcion.idActividad', '=', 'Actividad.idActividad')
           ->join('Persona', 'Inscripcion.idPersona', '=', 'Persona.idPersona')
           ->where('Actividad.idActividad', '=', $idActividad)
           ->where('Inscripcion.estado', '<>', 'Desinscripto' )
           ->select(['Persona.idPersona', 'Inscripcion.estado']);

       return $query->get()->toArray();
    }

    public function setFechaFinInscripcionesAttribute($value)
    {
        $this->attributes['fechaFinInscripciones'] = \Carbon\Carbon::parse($value);
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($actividad) { // before delete() method call this
            DB::beginTransaction();
            try {
                $inscripciones = $actividad->inscripciones();
                $inscripciones->delete();
                $actividad->puntosEncuentro()->delete();
                DB::commit();
            } catch (\Exception $exception) {
                DB::rollBack();
                throw new \Exception($exception->getMessage());
            }
        });

        static::updating(function ($actividad) { Auditoria::crear($actividad); });
    }
}
