<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class IntegranteResource extends Resource
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
            'idIntegrante' => $this->idIntegrante,
            'nombre' => $this->persona->getNombreCompletoAttribute(),
            'rol' => $this->rol,
            'estado'    => $this->estado,
            'fechaInicio' => ($this->fechaInicio)?$this->created_at->format('d/m/Y'):'',
            'fechaFin' => ($this->fechaFin)?$this->created_at->format('d/m/Y'):'',
        ];
    }
}
