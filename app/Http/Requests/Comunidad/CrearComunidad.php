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
            'fecha_diagnostico' => 'nullable|date',
            'plan_de_accion' => 'nullable',
            'fecha_plan_de_accion' => 'nullable|date',
            'fecha_fin_trabajo' => 'nullable|date',
            'activo' => 'required|boolean',
        ];
    }
}
