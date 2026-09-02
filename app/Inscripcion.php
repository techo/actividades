<?php

namespace App;

use App\Concerns\BelongsToCountry;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\SoftDeletes;

class Inscripcion extends Model
{
    use SoftDeletes;
    use BelongsToCountry; // scope por país vía la actividad (no tiene columna idPais propia)

    protected $table = 'Inscripcion';
    protected $primaryKey = 'idInscripcion';
    protected $dates = ['fechaInscripcion', 'deleted_at'];
    protected $guarded = ['idInscripcion'];
    protected $casts = [
        'roles_aplicados' => 'array',
        'inscripciones_aplicadas' => 'array',
    ];

    /**
     * Inscripcion no tiene columna de país: el país vive en su Actividad.
     * (Nota de performance: es un EXISTS correlacionado; si un listado grande lo
     * necesita más rápido, migrar a un join explícito a Actividad.)
     */
    public function applyCountryScope(Builder $builder, int $pais): void
    {
        $builder->whereHas('actividad', function ($q) use ($pais) {
            $q->where('Actividad.idPais', $pais);
        });
    }

    public function actividad()
    {
        return $this->belongsTo(Actividad::class, 'idActividad', 'idActividad');
    }

    public function persona()
    {
        return $this->belongsTo(Persona::class, 'idPersona', 'idPersona');
    }

    public function punto_encuentro()
    {
        return $this->belongsTo(PuntoEncuentro::class, 'idPuntoEncuentro', 'idPuntoEncuentro');
    }


    protected static function boot()
    {
        parent::boot();

        self::saving(function($inscripcion){
            if($usuario = Auth::user()) {
                $inscripcion->idPersonaModificacion = $usuario->idPersona;
            }

        });

        static::deleting(function ($inscripcion) {
            // Trazabilidad de la baja: el soft-delete NO dispara `saving`, así que
            // `idPersonaModificacion` queda con el último que EDITÓ la fila, no con
            // quien la dio de baja. Registramos la baja en auditoría con el usuario
            // autenticado actual (coordinador o el propio voluntario) para que una
            // desinscripción sea siempre rastreable.
            Auditoria::crear($inscripcion);

            //borrar registros de grupos
            GrupoRolPersona::where('idPersona', '=', $inscripcion->persona->idPersona)
                ->where('idActividad', '=', $inscripcion->actividad->idActividad)
                ->delete();
        });

        static::updating(function ($inscripcion) { Auditoria::crear($inscripcion); });

    }

    public function scopePresente($query)
    {
        return $query->where('presente', '=', '1' );
    }

    public function scopeAusente($query)
    {
        return $query->where('presente', '!=', '1' );
    }

    public function jornadas()
    {
        return $this->belongsToMany(Jornada::class, 'InscripcionJornada', 'idInscripcion', 'idJornada');
    }

    public function respuestas()
    {
        return $this->hasMany(InscripcionRespuesta::class, 'inscripcion_id', 'idInscripcion');
    }
}
