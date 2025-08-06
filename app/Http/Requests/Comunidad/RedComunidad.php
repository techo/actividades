<?php

namespace App\Http\Requests\Comunidad;

use Illuminate\Foundation\Http\FormRequest;

class RedComunidad extends FormRequest
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
            'tipo' => 'nullable',
            'relacion' => 'required',
            'presencia' => 'required',
            'nombre_contacto' => 'nullable',
            'telefono_contacto' => 'nullable',
            'mail_contacto' => 'nullable',
            'comentarios' => 'nullable',
        ];
    }
}
