<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

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

        ];

    const CREATED_AT = 'fecha_creacion';
    const UPDATED_AT = 'fecha_modificacion';

    public function tipo()
    {
        return $this->belongsTo(Tipo::class, 'idTipo', 'idTipo');
    }

    public function inscripciones()
    {
        return $this->hasMany(Inscripcion::class, 'idInscripcion');
    }

    public function puntosEncuentro()
    {
        return $this->hasMany(PuntoEncuentro::class, 'idActividad');
    }
}
