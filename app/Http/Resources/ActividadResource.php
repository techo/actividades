<?php

namespace App\Http\Resources;

use App\PuntoEncuentro;
use Illuminate\Http\Resources\Json\Resource;

class ActividadResource extends Resource
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
            'idActividad' => $this->idActividad,
            'tipo' => new TipoResource($this->tipo),
            'fechaInicio' => $this->fechaInicio->format('d-m-Y'),
            'fechaFin' => $this->fechaFin->format('d-m-Y'),
            'fechaInicioInscripciones' => $this->fechaInicioInscripciones->format('d-m-Y'),
            'fechaFinInscripciones' => $this->fechaFinInscripciones->format('d-m-Y'),
            'nombreActividad' => $this->nombreActividad,
            'descripcion' => $this->descripcion,
            'compromiso' => $this->compromiso,
            'costo' => $this->costo,
            'moneda' => $this->moneda,
            'puntosEncuentro' => PuntoEncuentroResource::collection($this->puntosEncuentro)
        ];
    }
}
