<?php

namespace App\Http\Resources;

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
            'fecha' => $this->fechaInicio->format('d/m'),
            'hora' => $this->fechaInicio->format('h:m'),
            'fechaInicio' => ActividadResource::convertirFecha($this->fechaInicio),
            'fechaFin' => ActividadResource::convertirFecha($this->fechaFin),
            'fechaInicioInscripciones' => ActividadResource::convertirFecha($this->fechaInicioInscripciones),
            'fechaFinInscripciones' => ActividadResource::convertirFecha($this->fechaFinInscripciones),
            'nombreActividad' => $this->nombreActividad,
            'descripcion' => $this->descripcion,
            'compromiso' => $this->compromiso,
            'costo' => $this->costo,
            'lugar' => $this->lugar,
            'moneda' => $this->moneda,
            'puntosEncuentro' => PuntoEncuentroResource::collection($this->puntosEncuentro),
            'localidad' => $this->localidad
        ];
    }

    private static function convertirFecha($fecha){
        if(is_null($fecha)) return '';

        return $fecha->format('d-m-Y');
    }
}
