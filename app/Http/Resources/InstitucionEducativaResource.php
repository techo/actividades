<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class InstitucionEducativaResource extends Resource
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
            'idInstitucionEducativa' => $this->idInstitucionEducativa,
            'nombre' => $this->nombre,
            'idPais'    => $this->idPair,
        ];
    }
}
