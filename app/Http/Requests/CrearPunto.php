<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class CrearPunto extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return request()->input('idPais') == auth()->user()->idPaisPermitido;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'punto' => 'required',
			'horario' => 'required',
			'idProvincia' => 'required',
			'idLocalidad' => 'required',
			'idPersona' => 'required',
			'estado' => 'required',
        ];
    }
}
