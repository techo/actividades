<?php

namespace App\Http\Requests;

use App\Provincia;
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
        $provincia = Provincia::findOrFail(request()->input('idProvincia'));
        return $provincia->id_pais == auth()->user()->idPaisPermitido;
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
