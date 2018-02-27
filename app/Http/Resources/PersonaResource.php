<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class PersonaResource extends Resource
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
            'idPersona' => $this->idPersona,
            'nombres' => $this->nombres,
            'apellido' => $this->apellido,
            'telefonoMovil' => $this->telefonoMovil,
            'mail' => $this->mail
        ];
    }
}
