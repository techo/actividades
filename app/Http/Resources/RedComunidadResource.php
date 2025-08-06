<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class RedComunidadResource extends Resource
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
            'idRedComunidad' => $this->idRedComunidad,
            'nombre' => $this->nombre,
            'tipo' => $this->tipo,
            'presencia' => $this->presencia,
            'estado'    => $this->estado?"Activo":"Inactivo",
        ];
    }
}
