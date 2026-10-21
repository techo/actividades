<?php

namespace App\Http\Requests\Equipo;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class CrearIntegrante extends FormRequest
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
     * Integrante castea fechaInicio/fechaFin como $dates: Eloquent solo acepta Y-m-d o
     * Y-m-d H:i:s, y cualquier otro formato que la regla `date` deja pasar (ej. 10/20/2026,
     * de navegadores sin date picker nativo) explotaba en fill() con 500. Se normaliza a Y-m-d.
     */
    protected function prepareForValidation()
    {
        foreach (['fechaInicio', 'fechaFin'] as $campo) {
            $valor = $this->input($campo);
            if (is_string($valor) && $valor !== '' && strtotime($valor) !== false) {
                $this->merge([$campo => Carbon::parse($valor)->format('Y-m-d')]);
            }
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'idPersona' => 'required',
            'idEquipo' => 'required',
            'idComunidad' => 'nullable',
            'rol' => 'required',
            'cargo' => 'nullable',
            'estado' => 'required|boolean',
            'despliegue' => 'required',
            'relacion' => 'required',
            'descripcion_rol' => 'nullable',
            'meta' => 'nullable|max:500',
            'hitos' => 'nullable|max:500',
            'dia_hora_reunion' => 'nullable',
            'periodicidad_reunion' => 'nullable',
            'impacto' => 'nullable|max:500',
            'capacidades' => 'nullable',
            'fechaInicio' => 'required|date',
            'fechaFin' => 'nullable|date|required_if:estado,0',
            'proyeccion' => 'nullable|integer',
        ];
    }
}
