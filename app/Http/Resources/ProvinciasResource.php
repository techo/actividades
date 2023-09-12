<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class ProvinciasResource extends Resource
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
            'idProvincia' => $this->id,
            'idPais'    => $this->pais_id,
            'nombre'    => $this->provincia,
            'provincia'    => $this->provincia,
        ];
    }
}
