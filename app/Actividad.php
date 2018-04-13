<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Actividad extends Model
{
    protected $table = "Actividad";
    protected $primaryKey = "idActividad";
    protected $guarded = ['idActividad'];
    protected $dates =
        [
            'feachaCreacion', 'fechaModificacion',
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

    public function puntosEncuentro()
    {
        return $this->hasMany(PuntoEncuentro::class, 'idActividad')->with('responsable');
    }

    public function unidadOrganizacional()
    {
        return $this->belongsTo(\App\UnidadOrganizacional::class, 'idUnidadOrganizacional', 'idUnidadOrganizacional');
    }

    public function modificadoPor()
    {
        return $this->belongsTo(Persona::class, 'idPersonaModificacion', 'idPersona');
    }

    public function scopePersonaInscripta($query, $idPersona) {
        return $this->inscripciones()->where('idPersona', $idPersona)->get()->count();
    }

    public function localidad()
    {
        return $this->hasOne(Localidad::class, 'id', 'idLocalidad');
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

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($actividad) { // before delete() method call this
            DB::beginTransaction();
            try {
                $actividad->escuelas()->delete();
                $actividad->puntosEncuentro()->delete();
                // ToDo: Enviar mail a los inscriptos
                $inscripciones = $actividad->inscripciones();

                foreach ($inscripciones as $inscripcion) {
                    DB::statement('DELETE FROM AsistenciaVoluntario WHERE idInscripcion = ' . $inscripcion->idInscripcion);
                    DB::statement('DELETE FROM Asignacion360 WHERE idInscripcion = ' . $inscripcion->idInscripcion
                        . ' OR idInscripcionEvaluado =' . $inscripcion->idInscripcion);

                }
                $sesiones = DB::select('SELECT idSesion FROM Sesion WHERE idActividad =  ?', [$actividad->idActividad]);
                foreach ($sesiones as $sesion) {
                    DB::statement('DELETE FROM AsistenciaPoblador where idSesion = ' . $sesion->idSesion);
                    DB::statement('DELETE FROM AsistenciaVoluntario where idSesion = ' . $sesion->idSesion);
                }


                DB::statement('DELETE FROM ActividadPresupuesto where idActividad = ' . $actividad->idActividad);
                DB::statement('DELETE FROM ActividadResponsable where idActividad = ' . $actividad->idActividad);
                DB::statement('DELETE FROM Campana where idActividad = ' . $actividad->idActividad);
                DB::statement('DELETE FROM Egreso where idActividad = ' . $actividad->idActividad);
                DB::statement('DELETE FROM FamiliaEnActividad where idActividad = ' . $actividad->idActividad);
                DB::statement('DELETE FROM ItemCuenta where idActividad = ' . $actividad->idActividad);
                DB::statement('DELETE FROM Localidad where idActividad = ' . $actividad->idActividad);
                DB::statement('DELETE FROM Sesion where idActividad = ' . $actividad->idActividad);
                DB::statement('DELETE FROM _EncuestaRespuestaActividad where idActividad = ' . $actividad->idActividad);
                $inscripciones->delete();
                DB::commit();
            } catch (\Exception $exception) {
                DB::rollBack();
                throw new \Exception($exception->getMessage());
            }
        });
    }
}
