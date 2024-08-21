<?php

namespace App\Http\Requests;


use App\Actividad;
use Illuminate\Foundation\Http\FormRequest;

class CrearJornada extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $actividad = Actividad::findOrFail(request()->input('idActividad'));
        return $actividad->idPais == auth()->user()->idPaisPermitido;
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
			'fechaInicio' => 'required',
			'fechaFin' => 'required',
			'idActividad' => 'required',
			'idPersona' => 'required',
			'activo' => 'required',
        ];
    }
}
