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

        $idPais    = $this->resolverIdPais($request);
        $idOficina = $request->filled('idOficina') ? (int) $request->input('idOficina') : null;
        $hoyAnio   = (int) now()->format('Y');
        $hoyMes    = (int) now()->format('n');

        // Columnas de la matriz. Cada columna es un (año, período):
        //  - anual: una columna por año de una ventana (años anteriores + actual +
        //    el próximo para planificar), período null.
        //  - resto: mismo año, una columna por índice de período de la granularidad.
        $columnas = [];
        if ($granularidad === 'anual') {
            for ($y = $anio - 4; $y <= $anio + 1; $y++) {
                $columnas[] = [
                    'anio'     => $y,
                    'periodo'  => null,
                    'etiqueta' => (string) $y,
                    'editable' => GranularidadPlan::editable('anual', null, $y, $hoyAnio, $hoyMes),
                ];
            }
        } else {
            foreach (GranularidadPlan::periodos($granularidad) as $p) {
                $columnas[] = [
                    'anio'     => $anio,
                    'periodo'  => $p,
                    'etiqueta' => GranularidadPlan::etiqueta($granularidad, $p, $anio),
                    'editable' => GranularidadPlan::editable($granularidad, $p, $anio, $hoyAnio, $hoyMes),
                ];
            }
        }

        $anios = array_values(array_unique(array_map(function ($c) { return $c['anio']; }, $columnas)));

        // Request base para el Real (mismo resolver que la API), acotado por país/oficina.
        $mkReq = function ($a) use ($idPais, $idOficina) {
            $params = ['anio' => $a];
            if ($idPais) {
                $params['idPais'] = $idPais;
            }
            if ($idOficina) {
                $params['idOficina'] = $idOficina;
            }
            return Request::create('', 'GET', $params);
        };

        // Planes vigentes de todos los años/granularidad en una sola query.
        $planes = $idPais ? PlanIndicador::vigentesIndexados($idPais, $idOficina, $anios, $granularidad) : [];

        $indicadores = [];
        foreach (MetricRegistry::catalogo() as $item) {
            $key        = $item['key'];
            $tipo       = MetricRegistry::tipoPeriodo($key);
            $acumulable = in_array($tipo, ['anio', 'fecha'], true);
            $esStock    = ($tipo === null || $tipo === 'ultimos_6m');

            // Real por año: serie mes×año (acumulable, se suma por período); snapshot
            // único (stock); o valor anual por año (no divisible: solo a nivel anual).
            $serie    = $acumulable ? (MetricRegistry::serieMensualPorAnios($key, $mkReq($anio), $anios) ?? []) : [];
            $snapshot = $esStock ? (MetricRegistry::resolver($key, $mkReq($anio))['value'] ?? null) : null;
            $anualNoDiv = [];
            if (!$acumulable && !$esStock && $granularidad === 'anual') {
                foreach ($anios as $a) {
                    $anualNoDiv[$a] = MetricRegistry::resolver($key, $mkReq($a))['value'] ?? null;
                }
            }

            $nota = $item['nota'];
            if ($esStock) {
                $aviso = 'Valor actual (indicador de stock): no depende del período seleccionado.';
                $nota  = $nota ? ($nota . ' ' . $aviso) : $aviso;
            } elseif (!$acumulable) {
                $aviso = 'Real disponible solo a nivel anual (no se desglosa por período).';
                $nota  = $nota ? ($nota . ' ' . $aviso) : $aviso;
            }

            $celdas = [];
            foreach ($columnas as $col) {
                $a = $col['anio'];
                $p = $col['periodo'];

                if ($acumulable) {
                    $meses = ($granularidad === 'anual') ? range(1, 12) : GranularidadPlan::meses($granularidad, $p);
                    $real  = 0;
                    foreach ($meses as $m) {
                        $real += $serie[$a][$m] ?? 0;
                    }
                } elseif ($esStock) {
                    $real = $snapshot;
                } else { // no divisible: solo anual
                    $real = ($granularidad === 'anual') ? ($anualNoDiv[$a] ?? null) : null;
                }

                $plan = $planes[$key . '|' . $a . '|' . ($p ?? 'A')] ?? null;

                $celdas[] = [
                    'anio'      => $a,
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
                'grupo'    => $item['grupo'],
                'es_stock' => $esStock,
                'celdas'   => $celdas,
            ];
        }

        return response()->json([
            'anio'         => $anio,
            'granularidad' => $granularidad,
            'idPais'       => $idPais,
            'paisNombre'   => $idPais ? optional(\App\Pais::find($idPais))->nombre : null,
            'idOficina'    => $idOficina,
            'periodos'     => $columnas,
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
        $anio      = (int) $datos['anio'];
        $idOficina = (isset($datos['idOficina']) && $datos['idOficina'] !== null && $datos['idOficina'] !== '')
            ? (int) $datos['idOficina']
            : null;

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
            $idOficina,
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
