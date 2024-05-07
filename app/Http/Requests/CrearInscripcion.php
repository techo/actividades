<?php

namespace App\Http\Requests;

use App\Actividad;
use Illuminate\Foundation\Http\FormRequest;

class CrearInscripcion extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $idActividad = array_slice(explode('/', request()->path()), array_search('actividades', explode('/', request()->path())) + 1, 1)[0];
        $idPaisActividad = Actividad::findOrFail($idActividad)->idPais;
        return $idPaisActividad == auth()->user()->idPaisPermitido;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            
        ];
    }
}
