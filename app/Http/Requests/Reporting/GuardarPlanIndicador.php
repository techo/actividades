<?php

namespace App\Http\Requests\Reporting;

use App\Reporting\GranularidadPlan;
use App\Reporting\MetricRegistry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class GuardarPlanIndicador extends FormRequest
{
    /**
     * El acceso base ya está restringido por role:admin en routes/web.php. Además,
     * replicamos el criterio de país del resto del backoffice (CrearActividad,
     * CrearHomeHeader, etc.): un admin con país asignado solo puede planificar SU
     * país; un admin "global" (idPaisPermitido 0/null) puede planificar cualquiera.
     * Sin esto, un admin de un país podía escribir el Plan de otro vía el idPais
     * del POST. La gobernanza de aprobación (aprobado_by) sigue siendo Fase 0.
     */
    public function authorize()
    {
        $paisUsuario = auth()->user()->idPaisPermitido ?? 0;

        if (!$paisUsuario) {
            return true;
        }

        return (int) $this->input('idPais') === (int) $paisUsuario;
    }

    public function rules()
    {
        return [
            'metric_key'        => 'required|string',
            'idPais'            => 'required|integer',
            'idOficina'         => 'nullable|integer',
            'anio'              => 'required|integer|min:2000|max:2100',
            'granularidad'      => 'required|in:mensual,trimestral,semestral,anual',
            'periodo'           => 'nullable|integer|min:1|max:12',
            'valor_planificado' => 'required|numeric|min:0',
        ];
    }

    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            if ($this->filled('metric_key') && !MetricRegistry::existe($this->input('metric_key'))) {
                $validator->errors()->add('metric_key', 'Métrica no encontrada en MetricRegistry.');
            }

            if ($this->filled('granularidad')) {
                $periodo = $this->input('periodo');
                $periodo = ($periodo === null || $periodo === '') ? null : (int) $periodo;
                if (!GranularidadPlan::periodoValido($this->input('granularidad'), $periodo)) {
                    $validator->errors()->add('periodo', 'Período inválido para la granularidad elegida.');
                }
            }
        });
    }
}
