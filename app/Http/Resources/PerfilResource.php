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
        $usuario = [
            'id'            => $this->idPersona,
            'email'         => $this->mail,
            'nombre'        => $this->nombres,
            'apellido'      => $this->apellidoPaterno,
            'nacimiento'    => $this->fechaNacimiento,
            'sexo'          => $this->sexo,
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
            'foto'          => ''
        ];

        if(file_exists(public_path('archivos/fotos-perfil/' . $this->foto))) {
            $usuario['foto'] = url('archivos/fotos-perfil/' . $this->foto);
        }

        return $usuario;
    }
}
