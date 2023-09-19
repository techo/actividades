<?php

namespace App\Http\Requests\Provincia;

use App\Localidad;
use App\Pais;
use App\Provincia;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class CrearLocalidad extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $provincia = Provincia::where('id', request()->idProvincia)->first();

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
            'idProvincia' => 'required',
            'id' => 'sometimes',
            'nombre' => 'required',
        ];
    }
}
