<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class PuntoEncuentroResource extends Resource
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
            'idPuntoEncuentro' => $this->idPuntoEncuentro,
            'punto' => $this->punto,
            'horario' => $this->horario,
            'provincia' => $this->provincia,
            'localidad' => $this->localidad,
            'responsable' => new PersonaResource($this->responsable),
            'borrable' => true,
        ];
    }
}
