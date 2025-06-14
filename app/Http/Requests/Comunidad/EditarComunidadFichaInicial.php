<?php

namespace App\Http\Requests\Comunidad;

use Illuminate\Foundation\Http\FormRequest;

class EditarComunidadFichaInicial extends FormRequest
{
    public function authorize()
    {
        return true; 
    }

    public function rules()
    {
        return [
            'idComunidad' => 'required|integer',
            'cantidad_familias' => 'nullable|integer',
            'cantidad_viviendas' => 'nullable|integer',
            'fecha_formacion' => 'nullable|date',
            'forma_constitucion' => 'nullable|string',
            'georeferencia' => 'nullable|string',
            'anio_inicio_techo' => 'nullable|digits:4|integer',
            'propietario_actual' => 'nullable|string',
            'estado_legalizacion' => 'nullable|string',
            'riesgo_eventos' => 'nullable|string',
            'riesgo_desalojo' => 'nullable|string',

            'riesgos_naturales' => 'nullable|array',
            'riesgos_antropicos' => 'nullable|array',

            'material_calle' => 'nullable|string',
            'acceso_electricidad' => 'nullable|string',
            'acceso_agua' => 'nullable|string',
            'manejo_aguas_residuales' => 'nullable|string',
            'manejo_aguas_pluviales' => 'nullable|string',

            'material_piso' => 'nullable|string',
            'material_pared' => 'nullable|string',
            'material_techo' => 'nullable|string',
            'alumbrado_publico' => 'nullable|string',
            'equipamientos' => 'nullable|array',

            'tiene_organizacion' => 'nullable|boolean',
            'liderazgos_electos' => 'nullable|boolean',
            'anio_eleccion' => 'nullable|digits:4|integer',
            'periodicidad_reunion' => 'nullable|string',
            'actividades_organizacion' => 'nullable|string',
            'otros_grupos' => 'nullable|boolean',
            'tipo_grupo' => 'nullable|string',

            'canales_comunicacion' => 'nullable|boolean',
            'tipo_comunicacion' => 'nullable|string',
        ];
    }
}
