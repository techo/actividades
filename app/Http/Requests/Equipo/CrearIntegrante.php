<?php

namespace App\Http\Requests\Equipo;

use Illuminate\Foundation\Http\FormRequest;

class CrearIntegrante extends FormRequest
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
            'idPersona' => 'required',
            'idEquipo' => 'required',
            'rol' => 'required',
            'estado' => 'required|boolean',
            'despliegue' => 'required',
            'relacion' => 'required',
            'fechaInicio' => 'required|date',
            'fechaFin' => 'nullable|date',
        ];
    }
}
