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
            'oficina' => $this->oficina->nombre,
            'idPais'    => $this->idPais,
            'nombre'    => $this->nombre,
            'estado'    => $this->activo?"Activo":"Inactivo",
            'area'      => $this->area,
            'fechaInicio' => ($this->fechaInicio)?$this->fechaInicio->format('d/m/Y'):'',
            'fechaFin' => ($this->fechaFin)?$this->fechaFin->format('d/m/Y'):'',
        ];
    }
}
