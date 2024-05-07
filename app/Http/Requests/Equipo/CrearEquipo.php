<?php

namespace App\Http\Requests\Equipo;

use Illuminate\Foundation\Http\FormRequest;

class CrearEquipo extends FormRequest
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
            'nombre' => 'required',
            'idOficina' => 'required',
            'fechaInicio' => 'required|date',
            'fechaFin' => 'required_if:activo,false|nullable|date|after:fechaInicio',
            'activo' => 'required|boolean',
            'area' => 'required',
        ];
    }
}
