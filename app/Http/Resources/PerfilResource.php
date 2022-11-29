<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class PerfilResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        if ($this->fichaMedica){
            $fichaMedica = $this->fichaMedica;
        } else {
            $fichaMedica = [
                'contacto_nombre' => '',
                'contacto_telefono' => '',
                'contacto_relacion' => '',
                'grupo_sanguinieo' => '',
                  'cobertura_nombre' => '',
                  'cobertura_numero' => '',
                  'confirma_datos' => '',
                  'idPersona' => ''
            ];
        }
        return [
            'id'            => $this->idPersona,
            'email'         => $this->mail,
            'nombre'        => $this->nombres,
            'apellido'      => $this->apellidoPaterno,
            'nacimiento'    => $this->fechaNacimiento,
            'genero'          => $this->genero,
            'dni'           => $this->dni,
            'pais'          => $this->idPais,
            'provincia'     => $this->idProvincia,
            'localidad'     => $this->idLocalidad,
            'telefono'      => $this->telefonoMovil,
            'google_id'     => $this->google_id,
            'facebook_id'   => $this->facebook_id,
            'recibirMails'  => $this->recibirMails,
            'acepta_marketing' => $this->acepta_marketing,
            'pass'          => '',
            'fichaMedica'          => $fichaMedica
        ];
    }
}
