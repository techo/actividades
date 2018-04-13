<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Inscripcion extends Model
{
    protected $table = 'Inscripcion';
    protected $primaryKey = 'idInscripcion';
    protected $dates = ['fechaInscripcion'];

    public function actividad()
    {
        return $this->belongsTo(Actividad::class, 'idActividad', 'idActividad');
    }

    public function persona()
    {
        return $this->belongsTo(Persona::class, 'idPersona', 'idPersona');
    }

    protected static function boot()
    {
        parent::boot();
        static::deleted(function ($inscripcion) { // before delete() method call this
            dd($inscripcion);
            DB::beginTransaction();
            try {
                DB::statement('DELETE FROM AsistenciaVoluntario WHERE idInscripcion = ' . $inscripcion->idInscripcion);
                DB::statement('DELETE FROM Asignacion360 WHERE idInscripcion = ' . $inscripcion->idInscripcion
                    . ' OR idInscripcionEvaluado =' . $inscripcion->idInscripcion);
                DB::commit();
            } catch (\Exception $exception) {
                DB::rollBack();
                throw new \Exception($exception->getMessage());
            }
        });

    }

}
