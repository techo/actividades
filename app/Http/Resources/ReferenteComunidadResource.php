<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class ReferenteComunidadResource extends Resource
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
            'idReferenteComunidad' => $this->idReferenteComunidad,
            'nombre' => $this->nombre,
            'rol' => $this->rol,
            'telefono' => $this->telefono,
            'documento' => $this->documento,
            'estado'    => $this->estado?"Activo":"Inactivo",
        ];
    }
}
