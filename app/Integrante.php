<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Integrante extends Model
{
    use SoftDeletes;
    protected $table = "Integrantes";
    protected $primaryKey = "idIntegrante";
    protected $fillable = [
        'idEquipo', 
        'rol', 
        'estado', 
        'despliegue', 
        'relacion', 
        'idPersona', 
        'fechaInicio', 
        'fechaFin',
        'archivo_carta_compromiso',
        'archivo_plan_de_trabajo',
        'descripcion_rol',
        'meta',
        'hitos',
        'dia_hora_reunion',
        'periodicidad_reunion',
        'impacto',
        'capacidades',
        'idComunidad'
    ];

    protected $dates =
        [
            'fechaInicio', 'fechaFin'
        ];

    protected $appends = ['participacion_status'];
    
    public function equipo()
    {
        return $this->belongsTo(Equipo::class, 'idEquipo', 'idEquipo');
    }

    public function reuniones()
    {
        return $this->belongsToMany(
            EquipoReunion::class,
            'equipo_reunion_persona', // tabla pivote
            'idPersona',              // FK en la pivote que apunta a Persona
            'idReunion',              // FK en la pivote que apunta a Reunion
            'idPersona',              // clave local en Integrante
            'idReunion'               // clave local en EquipoReunion
        );
    }

    public function persona()
    {
        return $this->hasOne(Persona::class, 'idPersona', 'idPersona');
    }

    public function comunidad()
    {
        return $this->hasOne(Comunidad::class, 'idComunidad', 'idComunidad' );
    }

    public function getParticipacionStatusAttribute()
    {
        $ahora = now();

        $reunionesMes = $this->reuniones()
            ->whereBetween('fecha', [$ahora->copy()->startOfMonth(), $ahora->copy()->endOfMonth()])
            ->count();

        if ($reunionesMes >1) {
            return 'onfire';
        } elseif ($reunionesMes === 1) {
            return 'comprometido';
        }

        $reunionesTrimestre = $this->reuniones()
            ->whereBetween('fecha', [$ahora->copy()->startOfQuarter(), $ahora->copy()->endOfQuarter()])
            ->count();

        if ($reunionesTrimestre >= 1) {
            return 'reactivar';
        }

        return 'sin_senial';
    }
}