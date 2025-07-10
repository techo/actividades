<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class EquipoReunionResource extends Resource
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
            'idEquipo'          => $this->idEquipo,
            'idReunion'          => $this->idReunion,
            'nombre' => $this->nombre,
            'fecha' => ($this->fecha)?$this->fecha->format('d/m/Y'):'',
            'despliegue'    => $this->despliegue,
            'descripcion'    => $this->descripcion,
            'compromisos'    => $this->compromisos,
            'personas'     => $this->personas->map(function ($p) {
                                return [
                                    'id' => $p->idPersona,
                                    'text' => $p->nombre_completo
                                ];
                            }),
        ];
    }
}
