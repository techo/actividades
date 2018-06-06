<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Grupo extends Model
{
    protected $table = 'Grupo';
    protected $primaryKey = 'idGrupo';
    protected $fillable = ['idGrupo', 'nombre', 'idPadre', 'idActividad'];

    public function actividad()
    {
        return $this->belongsTo(Actividad::class, 'idActividad');
    }

    public function getMiembrosAttribute()
    {
        return $this->grupos->merge($this->personas);
    }

    public function getPersonasAttribute()
    {
        return Persona::join('Grupo_Persona', 'Persona.idPersona', '=', 'Grupo_Persona.idPersona')
            ->where('Grupo_Persona.idActividad', '=', $this->idActividad)
            ->where('Grupo_Persona.idGrupo', '=', $this->idGrupo)
            ->get();
    }

    public function getGruposAttribute()
    {
        return Grupo::where('idPadre', '=', $this->idGrupo)
            ->get();
    }

    public function getCantidadMiembrosAttribute()
    {
        $miembros = $this->miembros;
        return $this->contarRecursivo($miembros);
    }
    private function contarRecursivo($lista)
    {
        $cuenta = 0;
        foreach ($lista as $item) {
            if ($this->esPersona($item)) {
                $cuenta++;
            } else {
                $cuenta += $item->contarRecursivo($item->miembros);
            }
        }
        return $cuenta;
    }

    private function esPersona($registro)
    {
        return !empty($registro->idPersona);
    }
}

/*
 * Esto debe devolver el arbol si es que existen grupos dentro del grupo
 *
 */