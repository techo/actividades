<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;
use App\Actividad;

class InscripcionResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $data = parent::toArray($request);
        $data['actividad'] = new ActividadResource(Actividad::find($data['idActividad']));
        return $data;
    }
}
