<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class ComunidadesResource extends Resource
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
            'idComunidad' => $this->idComunidad,
            'oficina' => $this->oficina?$this->oficina->nombre:'',
            'nombre'    => $this->nombre,
            'diagnostico'    => $this->diagnostico,
            'plan_de_accion'    => $this->plan_de_accion,
            'estado'    => $this->activo?"Activo":"Inactivo",
        ];
    }
}
