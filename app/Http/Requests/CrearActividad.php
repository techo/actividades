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
            'descripcionPago' => 'sometimes|nullable',
            'linkEvaluacion' => 'sometimes|nullable|url',
            'chat_grupal_whatsapp' => 'sometimes|nullable|url',
            'seguimiento_google' => 'sometimes|nullable',
            'requiere_ficha_medica' => 'required',
            'requiere_estudios' => 'required',
            'ficha_medica_campos' => 'sometimes',
            'roles_tags' => 'sometimes|nullable',
            'tipo_inscriptos_tag' => 'sometimes|nullable',

            'actividades_tags' => 'sometimes|nullable',
          //  'imagen_tarjeta' => 'sometimes|nullable',
           // 'destacada' => 'sometimes|nullable',
           // 'imagen_destacada' => 'sometimes|nullable',


            'acuerdo_especifico_url' => 'sometimes|nullable|url',
            'acuerdo_menores_url' => 'sometimes|nullable|url',

            'show_dates' => 'required',
            'show_location' => 'required',
        ];
    }
}
