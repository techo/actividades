<?php

namespace App\Http\Controllers\api\reporting;

use App\Http\Controllers\Controller;
use App\Reporting\MovilizacionMetrics;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * API de reporting (read-only) sobre las vistas reporting_*.
 *
 * Puerta de entrada para Power BI y otros consumidores SIN darles acceso directo
 * a la BD. Las vistas siguen siendo la fuente de verdad; esta API es solo
 * transporte (paginado + filtros).
 *
 * - Solo se exponen los datasets de la whitelist (NO reporting_person ni
 *   reporting_acceso_usuario, que son internos/seguridad).
 * - Hoy NO aplica scope por país: trae todo. `idPais`/`idOficina` quedan como
 *   filtros OPCIONALES por si se quiere acotar. El scope server-side se puede
 *   activar más adelante derivándolo del token.
 */
class ReportingController extends Controller
{
    /** alias público => vista/tabla real. */
    const DATASETS = [
        'fact_participacion'        => 'reporting_fact_participacion',
        'fact_membresia'            => 'reporting_fact_membresia',
        'fact_evaluacion_actividad' => 'reporting_fact_evaluacion_actividad',
        'fact_evaluacion_impacto'   => 'reporting_fact_evaluacion_impacto',
        'fact_lifecycle'            => 'reporting_fact_lifecycle',
        'dim_actividad'             => 'reporting_dim_actividad',
        'dim_equipo'                => 'reporting_dim_equipo',
        'dim_persona'               => 'reporting_dim_persona',
        'dim_geografia'             => 'reporting_dim_geografia',
        'dim_pais'                  => 'reporting_dim_pais',
        'dim_oficina'               => 'reporting_dim_oficina',
        'dim_indicador'             => 'reporting_dim_indicador',
        'snapshot_lifecycle'        => 'reporting_snapshot_lifecycle',
    ];

    /** Columnas por las que se permite filtrar (se aplican solo si existen en el dataset). */
    const FILTROS = ['anio', 'mes', 'idPais', 'idOficina', 'tipo_indicador', 'etapa', 'es_presente', 'es_kpi', 'vigente'];

    /** Catálogo autodescriptivo: qué datasets hay, sus columnas y filtros. */
    public function catalog()
    {
        $datasets = [];
        foreach (self::DATASETS as $alias => $vista) {
            $datasets[] = [
                'dataset'  => $alias,
                'columnas' => Schema::getColumnListing($vista),
                'url'      => url('/api/reporting/datasets/' . $alias),
            ];
        }

        return response()->json([
            'datasets' => $datasets,
            'filtros'  => self::FILTROS,
            'metricas' => [
                'movilizacion' => url('/api/reporting/metrics/movilizacion'),
            ],
        ]);
    }

    /** Filas paginadas de un dataset, con filtros opcionales. */
    public function dataset($name, Request $request)
    {
        if (!array_key_exists($name, self::DATASETS)) {
            return response()->json(['error' => 'Dataset no encontrado', 'datasets' => array_keys(self::DATASETS)], 404);
        }

        $vista    = self::DATASETS[$name];
        $columnas = Schema::getColumnListing($vista);
        $query    = DB::table($vista);

        foreach (self::FILTROS as $col) {
            if ($request->filled($col) && in_array($col, $columnas)) {
                $query->where($col, $request->input($col));
            }
        }

        $perPage = min((int) $request->input('per_page', 1000), 5000);

        return response()->json($query->paginate($perPage));
    }

    /** KPIs de movilización (definición única en App\Reporting\MovilizacionMetrics). */
    public function movilizacion(Request $request)
    {
        $anio    = $request->input('anio', now()->year);
        $pais    = $request->input('idPais');
        $oficina = $request->input('idOficina');

        return response()->json([
            'anio'              => (int) $anio,
            'movilizados_total' => MovilizacionMetrics::movilizadosTotal($anio, $pais, $oficina),
            'movilizados_kpi'   => MovilizacionMetrics::movilizadosKpi($anio, $pais, $oficina),
            'personas_unicas'   => MovilizacionMetrics::personasUnicas($anio, $pais, $oficina),
        ]);
    }
}
