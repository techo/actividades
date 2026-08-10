<?php

namespace App\Http\Controllers\backoffice\ajax;

use App\Http\Controllers\Controller;
use App\Http\Requests\Reporting\GuardarPlanIndicador;
use App\Reporting\MetricRegistry;
use App\Reporting\PlanIndicador;
use Illuminate\Http\Request;

/**
 * Prototipo de la pantalla de Indicadores (Plan vs. Real) — Fases 1 y 2 del plan
 * de trabajo (ver documento de análisis).
 *
 * A propósito NO se toca App\Reporting\MetricRegistry ni las vistas
 * reporting_*: el "Real" de cada indicador se sigue calculando exactamente igual
 * que hoy (y que en Power BI). Lo único nuevo es leer/escribir el "Plan" en
 * App\Reporting\PlanIndicador.
 *
 * Gobernanza: todavía no hay un flujo de aprobación (columna aprobado_by sin
 * usar por ahora). El acceso está limitado a role:admin en las rutas —
 * coordinador no ve ni edita nada — hasta que se defina quién aprueba cada
 * plan (Fase 0).
 */
class PlanIndicadorController extends Controller
{
    public function index(Request $request)
    {
        $anio = $request->filled('anio') ? (int) $request->input('anio') : (int) now()->format('Y');
        $mes  = $request->filled('mes') ? (int) $request->input('mes') : (int) now()->format('n');

        $idPais = $this->resolverIdPais($request);

        $paramsReal = ['anio' => $anio, 'mes' => $mes];
        if ($idPais) {
            $paramsReal['idPais'] = $idPais;
        }
        $realRequest = Request::create('', 'GET', $paramsReal);

        $indicadores = [];
        foreach (MetricRegistry::catalogo() as $item) {
            $real = MetricRegistry::resolver($item['key'], $realRequest);
            $plan = $idPais ? PlanIndicador::vigentePara($item['key'], $idPais, $anio, $mes) : null;

            $valorReal = $real['value'] ?? null;
            $valorPlan = $plan ? (float) $plan->valor_planificado : null;

            // Para indicadores "stock" (sin período) el Real es un snapshot al día
            // de hoy y no varía con el mes: lo aclaramos en la nota que la UI ya
            // muestra como tooltip, para que el Plan-vs-Real no se malinterprete.
            $nota = $item['nota'];
            if (!MetricRegistry::esPorPeriodo($item['key'])) {
                $aviso = 'Valor actual (indicador de stock): no depende del mes seleccionado.';
                $nota = $nota ? ($nota . ' ' . $aviso) : $aviso;
            }

            $indicadores[] = [
                'key'       => $item['key'],
                'nombre'    => $item['nombre'],
                'nota'      => $nota,
                'plan'      => $valorPlan,
                'real'      => $valorReal,
                'desempeno' => ($valorPlan !== null && $valorPlan > 0 && $valorReal !== null)
                    ? round($valorReal * 100 / $valorPlan)
                    : null,
            ];
        }

        return response()->json([
            'anio'        => $anio,
            'mes'         => $mes,
            'idPais'      => $idPais,
            'indicadores' => $indicadores,
        ]);
    }

    public function store(GuardarPlanIndicador $request)
    {
        $datos = $request->validated();

        $plan = PlanIndicador::guardarPlan(
            $datos['metric_key'],
            (int) $datos['idPais'],
            (int) $datos['anio'],
            isset($datos['mes']) ? (int) $datos['mes'] : null,
            (float) $datos['valor_planificado'],
            auth()->id()
        );

        return response()->json($plan->fresh());
    }

    /**
     * idPais explícito del filtro > país asignado al usuario (mismo criterio que
     * ya usa el resto del backoffice, ver banner "solo verás Actividades/Personas
     * de tu país") > null (deja elegir país en el front, ej. admin sin país
     * asignado).
     */
    private function resolverIdPais(Request $request): ?int
    {
        if ($request->filled('idPais')) {
            return (int) $request->input('idPais');
        }

        $idPaisUsuario = auth()->user()->idPaisPermitido ?? null;

        return $idPaisUsuario ? (int) $idPaisUsuario : null;
    }
}
