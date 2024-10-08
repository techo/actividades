<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class IntegranteResource extends Resource
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
            'equipo'          => $this->equipo,
            'idIntegrante' => $this->idIntegrante,
            'nombre' => $this->persona->getNombreCompletoAttribute(),
            'rol' => $this->rol,
            'estado'    => $this->estado?"Activo":"Inactivo",
            'despliegue'    => $this->despliegue,
            'relacion'    => $this->relacion,
            'fechaInicio' => ($this->fechaInicio)?$this->fechaInicio->format('d/m/Y'):'',
            'fechaFin' => ($this->fechaFin)?$this->fechaFin->format('d/m/Y'):'',
            'descripcion_rol'    => $this->descripcion_rol,
            'hitos'    => $this->hitos,
            'meta'    => $this->meta,
            'dia_hora_reunion'    => $this->dia_hora_reunion,
            'periodicidad_reunion'    => $this->periodicidad_reunion,
            'impacto'    => $this->impacto,
            'capacidades'    => $this->capacidades,
            'archivo_carta_compromiso'          => $this->archivo_carta_compromiso,
        ];
    }
}
