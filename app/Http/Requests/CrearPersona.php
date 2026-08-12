<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class CrearPersona extends FormRequest
{
    /**
     * Edad mínima requerida para registrarse.
     */
    const EDAD_MINIMA = 13;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // El registro es público. Un authorize() vacío devolvía null, que Laravel
        // interpreta como no autorizado → /api/register respondía 403 siempre.
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
            'mail' => 'required|unique:Persona,mail',
            'password' => 'required|confirmed',
            'nombres' => 'required',
            'apellidoPaterno' => 'required',
            'fechaNacimiento' => 'required|date|before_or_equal:' . Carbon::now()->subYears(self::EDAD_MINIMA)->format('Y-m-d'),
            'telefono' => 'required|integer',
            'telefonoMovil' => 'required|integer',
            'dni' => 'required|integer',
            'recibirMails' => 'required|boolean',
            'acepta_marketing' => 'required|boolean',
            'idPais' => 'required|integer',
            'idProvincia' => 'required|integer',
            'idLocalidad' => 'required|integer',
            'idUnidadOrganizacional' => 'required|integer',
        ];
    }

    /**
     * Mensajes de validación personalizados.
     *
     * El mensaje por defecto de `before_or_equal` habla de fechas, no de edad;
     * acá lo traducimos a un texto entendible para quien se registra.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'fechaNacimiento.before_or_equal' => __('validation.custom.fechaNacimiento.edad_minima', ['edad' => self::EDAD_MINIMA]),
        ];
    }
}
