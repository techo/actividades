<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\Resource;

class PersonaDatosResource extends Resource
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
            'dni' => $this->dni,
            'sexo' => $this->sexo,
            'nombres' => $this->nombres,
            'apellido' => $this->apellidoPaterno,
            'telefonoMovil' => $this->telefonoMovil,
            'mail' => $this->mail,
            'fechaNacimiento' => Carbon::parse($this->fechaNacimiento)->format('d-m-Y'),
            'pais' => $this->pais->nombre ?? 'N/A',
            'provincia' => $this->provincia->provincia ?? 'N/A',
            'localidad' => $this->localidad->localidad ?? 'N/A',

        ];;
    }
}
