<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class SuscriptosResource extends Resource
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
            'email'    => $this->mail,
            'perfil_seleccionado'    => $this->perfil_seleccionado,
            'tematica'    => $this->tematica,
            'tiempo_disponible'    => $this->tiempo_disponible,
            'created_at' => ($this->created_at)?$this->created_at->format('d/m/Y'):'',
        ];
    }
}
