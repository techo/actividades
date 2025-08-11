<?php

namespace App\Http\Requests\Comunidad;

use Illuminate\Foundation\Http\FormRequest;

class ReferenteComunidad extends FormRequest
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
            'idComunidad' => 'required',
            'nombre' => 'required',
            'rol' => 'nullable',
            'telefono' => 'required',
            'mail' => 'nullable',
            'comentarios' => 'nullable',
            'estado' => 'required'
        ];
    }
}
