<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class TipoResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
      return  [
            'idTipo' => $this->idTipo,
            'nombre' => $this->nombre,
            'imagen' => $this->imagen,
            'flujo'  => $this->flujo,
            'color' =>$this->categoria->color,
        ];
    }
}
