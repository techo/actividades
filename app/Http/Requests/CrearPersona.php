<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CrearPersona extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'mail' => 'required|unique:Persona,mail',
            'password' => 'required|confirmed',
            'nombres' => 'required',
            'apellidoPaterno' => 'required',
            'fechaNacimiento' => 'required|date',
            'telefono' => 'required|integer',
            'telefonoMovil' => 'required|integer',
            'dni' => 'required|integer',
            'recibirMails' => 'required|boolean',
            'acepta_marketing' => 'required|boolean',
            'idPais' => 'required|integer',
            'idProvincia' => 'required|integer',
            'idLocalidad' => 'required|integer',
            'idUnidadOrganizacional' => 'required|integer',
        ];
    }
}
