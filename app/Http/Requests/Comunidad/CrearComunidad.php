<?php

namespace App\Http\Requests\Comunidad;

use Illuminate\Foundation\Http\FormRequest;

class CrearComunidad extends FormRequest
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
            'idOficina' => 'nullable',
            'idLocalidad' => 'nullable',
            'idProvincia' => 'nullable',
            'diagnostico' => 'nullable',
            'plan_de_accion' => 'nullable',
            'activo' => 'required|boolean',
        ];
    }
}
