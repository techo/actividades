<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;


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

        self::saving(function($inscripcion){
            if($usuario = Auth::user()) {
                $inscripcion->idPersonaModificacion = $usuario->idPersona;
            }

        });

        static::deleted(function ($inscripcion) { // before delete() method call this
        });

        static::updating(function ($inscripcion) { Auditoria::crear($inscripcion); });

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
