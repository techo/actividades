<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class CrearActividad extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {

        Log::info(request());
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
            'nombreActividad' => 'required',
            'descripcion' => 'required',
            'estadoConstruccion' => 'required',
            'confirmacion' => 'required',
            'pago' => 'required',

            'idTipo' => 'required',
            'idOficina' => 'required',

            'fechaInicio' => 'required|date',
            'fechaFin' => 'required|date',

            'fechaInicioInscripciones' => 'sometimes|date|before_or_equal:fechaInicio',
            'fechaFinInscripciones' => 'required_with:fechaInicioInscripciones|after_or_equal:fechaInicioInscripciones',
            'fechaInicioEvaluaciones' => 'sometimes|date|after_or_equal:fechaFin',
            'fechaFinEvaluaciones' => 'required_with:fechaInicioEvaluaciones|after_or_equal:fechaInicioEvaluaciones',
            'calculaFecha' => 'required',
            
            'lugar' => 'required',
            'idPais' => 'required',
            'idProvincia' => 'required',
            'idLocalidad' => 'required',

            'limiteInscripciones' => 'nullable',
            'inscripcionInterna' => 'nullable',
            'mensajeInscripcion' => 'required',
            
            'montoMin' => "required_if:pago,1|numeric",
            'montoMax' => 'sometimes|nullable',
            'moneda' => 'sometimes|nullable',
            'fechaLimitePago' => 'sometimes',
            'beca' => 'sometimes|nullable|url',
            'linkPago' => 'sometimes|nullable|url',
            'linkEvaluacion' => 'sometimes|nullable|url',
            'seguimiento_google' => 'sometimes|nullable',
        ];
    }
}
