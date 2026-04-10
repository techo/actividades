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
            'mail' => $this->mail,
            'nombre' => $this->nombre,
            'apellido' => $this->apellido,
            'dni' => $this->dni,
            'genero' => $this->genero,
            'fecha_nacimiento' => ($this->fecha_nacimiento)?$this->fecha_nacimiento->format('Y-m-d'):'',
            'telefono'  => $this->telefono,
            'idProvincia' => $this->idProvincia,
            'idLocalidad' => $this->idLocalidad,
            'ocupacion_actual' => $this->ocupacion_actual,
            'canal_contacto' => $this->canal_contacto,
            'experiencia_previa' => $this->experiencia_previa,
            'instagram' => $this->instagram,
            'campaign_id' => $this->campaign_id,
            'convertido' => $this->convertido,
            'filtro_categorias'   => $this->filtro_categorias,
            'filtro_ubicaciones' => $this->filtro_ubicaciones,
            'perfil_seleccionado'    => $this->perfil_seleccionado,
            'tematica'    => $this->tematica,
            'tiempo_disponible'    => $this->tiempo_disponible,
            'created_at' => ($this->created_at)?$this->created_at->format('d/m/Y'):'',
        ];
    }
}
