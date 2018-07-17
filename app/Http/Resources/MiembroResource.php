<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class MiembroResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'        => ($this->idPersona) ? $this->idPersona : $this->idGrupo,
            'tipo'      => ($this->idPersona) ? 'persona' : 'grupo',
            'nombre'    => ($this->idPersona) ? $this->nombres . ' ' . $this->apellidoPaterno : $this->nombre,
            'dni'       => $this->when($this->dni, $this->dni),
            'rol'       => ($this->idPersona) ?  $this->rol : '-',
            'cantidad'  => ($this->idPersona) ?  '-' : $this->cantidadMiembros
        ];
    }
}
