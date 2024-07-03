<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class MisActividadesResource extends Resource
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
            'tipo' => $this->tipo->nombre,
            'tipoColor' => $this->tipo->color,
            'fecha' => empty($this->fechaInicio) ? '' : $this->fechaInicio->format('d/m/Y'),
            'hora' => empty($this->fechaInicio) ? '' : $this->fechaInicio->format('H:i'),
            'fechaInicio' => empty($this->fechaInicio) ? '' : $this->fechaInicio->format('d/m/Y H:i'),
            'fechaFin' => empty($this->fechaFin) ? '' : $this->fechaFin->format('d/m/Y H:i'),
            'fechaInicioInscripciones' =>empty($this->fechaInicioInscripciones) ? '' : $this->fechaInicioInscripciones->format('d/m/Y H:i'),
            'fechaFinInscripciones' => empty($this->fechaFinInscripciones) ? '' : $this->fechaFinInscripciones->format('d/m/Y H:i'),
            'fechaInicioEvaluaciones' =>empty($this->fechaInicioEvaluaciones) ? '' : $this->fechaInicioEvaluaciones->format('d/m/Y H:i'),
            'fechaFinEvaluaciones' => empty($this->fechaFinEvaluaciones) ? '' : $this->fechaFinEvaluaciones->format('d/m/Y H:i'),
            'nombreActividad' => $this->nombreActividad,
            'show_dates' => $this->show_dates,
            'show_location' => $this->show_location,
            'lugar' => empty($this->provincia) ? 'Indefinido' : $this->provincia->provincia . ", " . $this->pais->nombre,
            'puntoEncuentro' => empty($this->puntosEncuentro) ? null : $this->puntosEncuentro,
            'presente' => (isset($this->presente) && $this->presente == 1) ? 1 : 0,
            'img' => $this->imagen,
            'descripcion' => clean_string($this->descripcion)
        ];
    }
}
