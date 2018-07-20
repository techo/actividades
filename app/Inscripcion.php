<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


class Inscripcion extends Model
{
    protected $table = 'Inscripcion';
    protected $primaryKey = 'idInscripcion';
    protected $dates = ['fechaInscripcion'];
    protected $guarded = ['idInscripcion'];

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
        static::deleted(function ($inscripcion) { // before delete() method call this
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

    public function scopeInscripto($query)
    {
        return $query->where('estado', '<>', 'Desinscripto' );
    }

    public function scopePresente($query)
    {
        return $query->where('presente', '=', '1' );
    }

}
