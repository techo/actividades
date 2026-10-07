<?php

namespace App\Http\Resources;

use App\Services\Documento\DocumentoService;
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
        if ($this->fichaMedica) {
            $fichaMedica = $this->fichaMedica;
        } else {
            $fichaMedica = [
                'contacto_nombre' => '',
                'contacto_telefono' => '',
                'contacto_relacion' => '',
                'grupo_sanguinieo' => '',
                'cobertura_nombre' => '',
                'cobertura_numero' => '',
                'archivo_medico' => '',
                'confirma_datos' => '',
                'enfermedades_preexistentes' => '',
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
            // Tipo de documento: el guardado, o —para personas legacy que nunca lo
            // eligieron— el auto-detectado desde su documento actual (así el selector
            // del perfil arranca en el tipo correcto y no marca error falso). Si no se
            // puede detectar, va null y el front cae al default del país.
            'tipo_documento' => $this->tipo_documento
                ?: (new DocumentoService())->validar($this->idPais, $this->dni)['tipo'],
            'instagram'           => $this->instagram,
            'pais'          => $this->idPais,
            'provincia'     => $this->idProvincia,
            'localidad'     => $this->idLocalidad,
            'telefono'      => $this->telefonoMovil,
            'google_id'     => $this->google_id,
            'facebook_id'   => $this->facebook_id,
            'recibirMails'  => $this->recibirMails,
            'acepta_marketing' => $this->acepta_marketing,
            'pass'          => '',
            'fichaMedica'          => $fichaMedica,
            'estudios'          => $this->estudios,
            'photo'          => $this->photo,
            'integrantes'          => IntegranteResource::collection($this->integrantes()->with('equipo')->get()),
        ];
    }
}
