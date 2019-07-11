<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class UsuariosResource extends Resource
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
            'id' => $this->idPersona,
            'dni'       => $this->dni,
            'nombre'    => $this->nombres,
            'apellido'    => $this->apellidoPaterno,
            'email'    => $this->mail,
            'created_at' => ($this->created_at)?$this->created_at->format('d/m/Y'):'',
        ];
    }
}
