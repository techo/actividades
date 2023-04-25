<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class EquiposResource extends Resource
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
            'idEquipo' => $this->idEquipo,
            'idOficina' => $this->idOficina,
            'idPais'    => $this->idPais,
            'nombre'    => $this->nombre,
            'activo'    => $this->tematica,
            'tiempo_disponible'    => $this->tiempo_disponible,
            'fechaInicio' => ($this->fechaInicio)?$this->created_at->format('d/m/Y'):'',
            'fechaFin' => ($this->fechaFin)?$this->created_at->format('d/m/Y'):'',
        ];
    }
}
