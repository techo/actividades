<?php

namespace App\Http\Controllers\backoffice\ajax;

use App\Http\Controllers\Controller;
use App\Http\Requests\Reporting\GuardarPlanIndicador;
use App\Reporting\GranularidadPlan;
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

        $granularidad = (string) $request->input('granularidad', 'trimestral');
        if (!GranularidadPlan::valida($granularidad)) {
            $granularidad = 'trimestral';
        }

        $idPais  = $this->resolverIdPais($request);
        $hoyAnio = (int) now()->format('Y');
        $hoyMes  = (int) now()->format('n');

        // Columnas de la matriz: un período por índice de la granularidad, con su
        // etiqueta y si todavía es editable (período no cerrado).
        $periodos = [];
        foreach (GranularidadPlan::periodos($granularidad) as $p) {
            $periodos[] = [
                'periodo'  => $p,
                'etiqueta' => GranularidadPlan::etiqueta($granularidad, $p, $anio),
                'editable' => GranularidadPlan::editable($granularidad, $p, $anio, $hoyAnio, $hoyMes),
            ];
        }

        // Real: mismo resolver que la API, acotado por país/año.
        $paramsReal = ['anio' => $anio];
        if ($idPais) {
            $paramsReal['idPais'] = $idPais;
        }
        $realRequest = Request::create('', 'GET', $paramsReal);

        // Planes vigentes de todo el país/año/granularidad en una sola query.
        $planes = $idPais ? PlanIndicador::vigentesIndexados($idPais, $anio, $granularidad) : [];

        $indicadores = [];
        foreach (MetricRegistry::catalogo() as $item) {
            $key        = $item['key'];
            $tipo       = MetricRegistry::tipoPeriodo($key);
            $acumulable = in_array($tipo, ['anio', 'fecha'], true);
            $esStock    = ($tipo === null || $tipo === 'ultimos_6m');

            // Real: serie mensual (acumulable, se suma por período) o valor único
            // (stock = snapshot; no divisible = solo tiene sentido a nivel anual).
            $serie      = $acumulable ? (MetricRegistry::serieMensual($key, $realRequest) ?? []) : [];
            $valorUnico = ($esStock || !$acumulable) ? (MetricRegistry::resolver($key, $realRequest)['value'] ?? null) : null;

            $nota = $item['nota'];
            if ($esStock) {
                $aviso = 'Valor actual (indicador de stock): no depende del período seleccionado.';
                $nota  = $nota ? ($nota . ' ' . $aviso) : $aviso;
            } elseif (!$acumulable) {
                $aviso = 'Real disponible solo a nivel anual (no se desglosa por período).';
                $nota  = $nota ? ($nota . ' ' . $aviso) : $aviso;
            }

            $celdas = [];
            foreach ($periodos as $col) {
                $p = $col['periodo'];

                if ($acumulable) {
                    $real = 0;
                    foreach (GranularidadPlan::meses($granularidad, $p) as $m) {
                        $real += $serie[$m] ?? 0;
                    }
                } elseif ($esStock) {
                    $real = $valorUnico;
                } else { // no divisible: solo anual
                    $real = ($granularidad === 'anual') ? $valorUnico : null;
                }

                $plan = $planes[$key . '|' . ($p ?? 'A')] ?? null;

                $celdas[] = [
                    'periodo'   => $p,
                    'plan'      => $plan,
                    'real'      => $real,
                    'editable'  => $col['editable'],
                    'desempeno' => ($plan !== null && $plan > 0 && $real !== null)
                        ? round($real * 100 / $plan)
                        : null,
                ];
            }

            $indicadores[] = [
                'key'      => $key,
                'nombre'   => $item['nombre'],
                'nota'     => $nota,
                'es_stock' => $esStock,
                'celdas'   => $celdas,
            ];
        }

        return response()->json([
            'anio'         => $anio,
            'granularidad' => $granularidad,
            'idPais'       => $idPais,
            'periodos'     => $periodos,
            'indicadores'  => $indicadores,
        ]);
    }

    public function store(GuardarPlanIndicador $request)
    {
        $datos = $request->validated();

        $granularidad = $datos['granularidad'];
        $periodo = (isset($datos['periodo']) && $datos['periodo'] !== null && $datos['periodo'] !== '')
            ? (int) $datos['periodo']
            : null;
        $anio = (int) $datos['anio'];

        // Defensa server-side: no permitir modificar un período ya cerrado, aunque
        // el front deshabilite el input.
        $hoyAnio = (int) now()->format('Y');
        $hoyMes  = (int) now()->format('n');
        if (!GranularidadPlan::editable($granularidad, $periodo, $anio, $hoyAnio, $hoyMes)) {
            return response()->json(['error' => 'El período ya cerró: no se puede modificar el plan.'], 422);
        }

        $plan = PlanIndicador::guardarPlan(
            $datos['metric_key'],
            (int) $datos['idPais'],
            $anio,
            $granularidad,
            $periodo,
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
