<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class LocalidadesResource extends Resource
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
            'idLocalidad' => $this->id,
            'idProvincia' => $this->id_provincia,
            'nombre'    => $this->localidad,
            'localidad'    => $this->localidad,
        ];
    }
}
