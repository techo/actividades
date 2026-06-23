<?php

namespace App\Reporting;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Registro de métricas de reporting (capa semántica).
 *
 * Cada métrica se define UNA vez acá (nombre, vista, medida, filtros, manejo de
 * período) y se sirve por el endpoint genérico /api/reporting/metrics/{key}, con
 * filtros idPais / anio / mes / idOficina y group_by opcional. Agregar una métrica
 * nueva = una entrada en este array, no una ruta nueva.
 *
 * Las definiciones (qué es movilizado, KPI, mesa, etc.) viven en las vistas
 * reporting_*; acá solo se elige medida + filtros. La app no redefine reglas.
 *
 * medida: 'count' | ['sum', col] | ['count_distinct', col]
 * periodo: 'anio' (cols anio/mes) | ['fecha', col] | ['anio_col', col] |
 *          'overlap' (fecha_inicio/fecha_fin) | 'ultimos_6m' (+col) | null
 */
class MetricRegistry
{
    private static function defs(): array
    {
        $part = 'reporting_fact_participacion';
        $memb = 'reporting_fact_membresia';
        $sol  = 'reporting_fact_solucion';
        $com  = 'reporting_dim_comunidad';

        return [
            // ── Movilización (SUM(es_presente) sobre fact_participacion) ──
            'movilizados_territorio' => ['nombre' => 'Nº Voluntarios/as movilizados/as a actividades en territorio', 'vista' => $part, 'medida' => ['sum', 'es_presente'], 'filtros' => ['tipo_indicador' => 'territorio'], 'periodo' => 'anio'],
            'movilizados_colecta' => ['nombre' => 'Nº Voluntarios/as movilizados/as en Colecta', 'vista' => $part, 'medida' => ['sum', 'es_presente'], 'filtros' => ['tipo_indicador' => 'colecta'], 'periodo' => 'anio'],
            'movilizados_construcciones' => ['nombre' => 'Nº Voluntarios/as movilizados/as en construcciones', 'vista' => $part, 'medida' => ['sum', 'es_presente'], 'filtros' => ['tipo_indicador' => 'construccion_de_viviendas'], 'periodo' => 'anio'],
            'movilizados_otras' => ['nombre' => 'Nº Voluntarios/as movilizados/as en otras actividades', 'vista' => $part, 'medida' => ['sum', 'es_presente'], 'where_raw' => "(tipo_indicador IS NULL OR tipo_indicador NOT IN ('territorio','colecta','construccion_de_viviendas'))", 'periodo' => 'anio'],
            'movilizados_total' => ['nombre' => 'Voluntarios/as movilizados/as en actividades (TOTAL)', 'vista' => $part, 'medida' => ['sum', 'es_presente'], 'periodo' => 'anio', 'group_by' => ['tipo_indicador']],

            // ── Equipo permanente (personas únicas vigentes sobre fact_membresia) ──
            'equipo_permanente_total' => ['nombre' => 'Voluntarios/as en equipo permanente (TOTAL)', 'vista' => $memb, 'medida' => ['count_distinct', 'person_key'], 'filtros' => ['vigente' => 1], 'periodo' => null, 'group_by' => ['area', 'rol']],
            'permanentes_areas' => ['nombre' => 'Voluntarios/as permanentes en áreas', 'vista' => $memb, 'medida' => ['count_distinct', 'person_key'], 'filtros' => ['vigente' => 1], 'where_raw' => 'idComunidad IS NULL', 'periodo' => null],
            'permanentes_comunidades' => ['nombre' => 'Voluntarios/as permanentes en equipos de comunidades', 'vista' => $memb, 'medida' => ['count_distinct', 'person_key'], 'filtros' => ['vigente' => 1], 'where_raw' => 'idComunidad IS NOT NULL', 'periodo' => null],
            'coordinaciones_comunidad' => ['nombre' => 'N° de voluntarias(os) en coordinaciones de comunidad', 'vista' => $memb, 'medida' => ['count_distinct', 'person_key'], 'filtros' => ['vigente' => 1, 'rol' => 'coordinacion'], 'where_raw' => 'idComunidad IS NOT NULL', 'periodo' => null],

            // ── Campañas y encuentros ──
            'campanas_captacion_nacional' => ['nombre' => 'Cantidad de campañas nacionales de captación de voluntariado ejecutadas', 'vista' => 'reporting_fact_campania', 'medida' => 'count', 'filtros' => ['tipo' => 'captacion', 'es_nacional' => 1], 'periodo' => 'overlap'],
            'encuentros' => ['nombre' => 'N° de encuentros locales/nacionales realizados para el voluntariado', 'vista' => 'reporting_dim_actividad', 'medida' => 'count', 'filtros' => ['tipo_indicador' => 'encuentros'], 'periodo' => 'anio', 'nota' => 'El split local/nacional (group_by alcance) está vacío hasta backfillear Actividad.alcance.'],

            // ── Comunidades y mesas ──
            'comunidades_activas' => ['nombre' => 'N° de comunidades activas', 'vista' => $com, 'medida' => 'count', 'filtros' => ['activo' => 1], 'periodo' => null],
            'comunidades_ficha_finalizada' => ['nombre' => 'N° de comunidades con ficha de asentamiento finalizada', 'vista' => $com, 'medida' => 'count', 'filtros' => ['tiene_ficha' => 1], 'periodo' => null, 'nota' => 'Usa "tiene ficha"; el criterio de campos clave completos está pendiente.'],
            'comunidades_inicio_trabajo' => ['nombre' => 'N° de comunidades con las que iniciamos el trabajo en el periodo', 'vista' => $com, 'medida' => 'count', 'periodo' => ['anio_col', 'anio_inicio_techo']],
            'comunidades_tenencia_regularizada' => ['nombre' => 'N° de comunidades con situación de tenencia de la tierra regularizada', 'vista' => $com, 'medida' => 'count', 'filtros' => ['estado_legalizacion' => 'legal'], 'periodo' => null, 'nota' => 'Confirmar el valor exacto de estado_legalizacion que cuenta como regularizada.'],
            'comunidades_fin_trabajo' => ['nombre' => 'N° de comunidades donde finalizamos el trabajo', 'vista' => $com, 'medida' => 'count', 'where_raw' => 'fecha_fin_trabajo IS NOT NULL', 'periodo' => ['fecha', 'fecha_fin_trabajo']],
            'mesas_activas' => ['nombre' => 'N° de mesas de trabajo activas', 'vista' => $com, 'medida' => 'count', 'filtros' => ['activo' => 1, 'tiene_diagnostico' => 1], 'periodo' => ['ultimos_6m', 'fecha_diagnostico']],
            'mesas_implementadas' => ['nombre' => 'N° de mesas de trabajo implementadas en el período', 'vista' => $com, 'medida' => 'count', 'filtros' => ['tiene_plan' => 1], 'periodo' => ['fecha', 'fecha_plan_de_accion']],
            'vecinos_mesa' => ['nombre' => 'N° de vecinas(os) participando en mesa de trabajo', 'vista' => $sol, 'medida' => ['sum', 'numero_participantes'], 'where_raw' => 'idComunidad IN (SELECT idComunidad FROM reporting_dim_comunidad WHERE tiene_diagnostico = 1 OR tiene_plan = 1)', 'periodo' => 'anio'],

            // ── Impacto / soluciones (SUM(total_soluciones) por tipo_solucion) ──
            'viviendas_emergencia' => ['nombre' => 'N° de viviendas de emergencia construidas en el periodo', 'vista' => $sol, 'medida' => ['sum', 'total_soluciones'], 'filtros' => ['tipo_solucion' => 'vivienda_emergencia'], 'periodo' => 'anio'],
            'viviendas_transitorias' => ['nombre' => 'N° de viviendas transitorias/progresivas construidas en el periodo', 'vista' => $sol, 'medida' => ['sum', 'total_soluciones'], 'filtros' => ['tipo_solucion' => 'vivienda_transitoria'], 'periodo' => 'anio'],
            'viviendas_permanentes' => ['nombre' => 'N° de viviendas permanentes/sociales construidas en el periodo', 'vista' => $sol, 'medida' => ['sum', 'total_soluciones'], 'filtros' => ['tipo_solucion' => 'vivienda_social'], 'periodo' => 'anio'],
            'familias_agua' => ['nombre' => 'N° de familias que cuentan con una solucion de agua en el periodo', 'vista' => $sol, 'medida' => ['sum', 'total_soluciones'], 'filtros' => ['tipo_solucion' => 'solucion_agua_familiar'], 'periodo' => 'anio'],
            'familias_saneamiento' => ['nombre' => 'N° de familias que cuentan con una solución de saneamiento mejorada', 'vista' => $sol, 'medida' => ['sum', 'total_soluciones'], 'filtros' => ['tipo_solucion' => 'solucion_saneamiento_familiar'], 'periodo' => 'anio'],
            'familias_energia' => ['nombre' => 'N° de familias que cuentan con una solucion de energia eléctrica en el periodo', 'vista' => $sol, 'medida' => ['sum', 'total_soluciones'], 'filtros' => ['tipo_solucion' => 'solucion_energia_familiar'], 'periodo' => 'anio'],
            'infraestructura_comunitaria' => ['nombre' => 'N° de proyectos de infraestructura comunitaria ejecutados en el periodo', 'vista' => $sol, 'medida' => ['sum', 'total_soluciones'], 'filtros' => ['tipo_solucion' => 'solucion_infraestructura_comunitaria'], 'periodo' => 'anio'],
            'soluciones_agua_comunitaria' => ['nombre' => 'Nº de soluciones comunitarias de acceso a agua en el periodo', 'vista' => $sol, 'medida' => ['sum', 'total_soluciones'], 'filtros' => ['tipo_solucion' => 'solucion_agua_comunitaria'], 'periodo' => 'anio'],
            'soluciones_energia_comunitaria' => ['nombre' => 'Nº de soluciones comunitarias de acceso a energía e iluminación en el periodo', 'vista' => $sol, 'medida' => ['sum', 'total_soluciones'], 'filtros' => ['tipo_solucion' => 'solucion_energia_comunitaria'], 'periodo' => 'anio'],
            'soluciones_saneamiento_comunitaria' => ['nombre' => 'Nº de soluciones comunitarias de saneamiento en el periodo', 'vista' => $sol, 'medida' => ['sum', 'total_soluciones'], 'filtros' => ['tipo_solucion' => 'solucion_saneamiento_comunitario'], 'periodo' => 'anio'],
            'sedes_comunitarias' => ['nombre' => 'N° de sedes comunitarias construidas en el periodo', 'vista' => $sol, 'medida' => ['sum', 'total_soluciones'], 'filtros' => ['tipo_solucion' => 'sede_comunitaria'], 'periodo' => 'anio'],
        ];
    }

    /** Catálogo: lista de métricas disponibles (key + nombre + nota). */
    public static function catalogo(): array
    {
        $items = [];
        foreach (self::defs() as $key => $def) {
            $items[] = [
                'key'    => $key,
                'nombre' => $def['nombre'],
                'nota'   => $def['nota'] ?? null,
                'url'    => url('/api/reporting/metrics/' . $key),
            ];
        }
        return $items;
    }

    public static function existe(string $key): bool
    {
        return array_key_exists($key, self::defs());
    }

    /** Calcula una métrica con los filtros del request. Devuelve array para JSON. */
    public static function resolver(string $key, Request $request)
    {
        $defs = self::defs();
        if (!isset($defs[$key])) {
            return null;
        }
        $def      = $defs[$key];
        $vista    = $def['vista'];
        $columnas = Schema::getColumnListing($vista);

        $anio = $request->filled('anio') ? (int) $request->input('anio') : null;
        $mes  = $request->filled('mes') ? (int) $request->input('mes') : null;

        $base = function () use ($vista, $def, $columnas, $request, $anio, $mes) {
            $q = DB::table($vista);

            // Filtros fijos de la métrica.
            foreach (($def['filtros'] ?? []) as $col => $val) {
                $q->where($col, $val);
            }
            if (!empty($def['where_raw'])) {
                $q->whereRaw($def['where_raw']);
            }

            // Geo (opcional, si la columna existe).
            foreach (['idPais', 'idOficina'] as $g) {
                if ($request->filled($g) && in_array($g, $columnas)) {
                    $q->where($g, $request->input($g));
                }
            }

            // Período.
            self::aplicarPeriodo($q, $def['periodo'] ?? null, $columnas, $anio, $mes);

            return $q;
        };

        [$selectExpr] = self::medidaExpr($def['medida']);

        // group_by opcional.
        $groupBy = $request->input('group_by');
        $permitido = $def['group_by'] ?? [];
        if ($groupBy && in_array($groupBy, $permitido) && in_array($groupBy, $columnas)) {
            $rows = $base()
                ->selectRaw($groupBy . ' as grupo, ' . $selectExpr . ' as value')
                ->groupBy($groupBy)
                ->get();
            return [
                'key'      => $key,
                'nombre'   => $def['nombre'],
                'group_by' => $groupBy,
                'data'     => $rows,
                'nota'     => $def['nota'] ?? null,
            ];
        }

        $value = $base()->selectRaw($selectExpr . ' as value')->value('value');

        return [
            'key'     => $key,
            'nombre'  => $def['nombre'],
            'value'   => (int) $value,
            'filtros' => array_filter([
                'anio'      => $anio,
                'mes'       => $mes,
                'idPais'    => $request->input('idPais'),
                'idOficina' => $request->input('idOficina'),
            ], function ($v) { return $v !== null; }),
            'nota'    => $def['nota'] ?? null,
        ];
    }

    private static function medidaExpr($medida): array
    {
        if ($medida === 'count') {
            return ['COUNT(*)'];
        }
        [$tipo, $col] = $medida;
        if ($tipo === 'sum') {
            return ['COALESCE(SUM(`' . $col . '`), 0)'];
        }
        if ($tipo === 'count_distinct') {
            return ['COUNT(DISTINCT `' . $col . '`)'];
        }
        return ['COUNT(*)'];
    }

    private static function aplicarPeriodo($q, $periodo, array $columnas, $anio, $mes): void
    {
        if (!$periodo) {
            return;
        }

        // Período por columnas anio/mes del hecho.
        if ($periodo === 'anio') {
            if ($anio !== null && in_array('anio', $columnas)) $q->where('anio', $anio);
            if ($mes !== null && in_array('mes', $columnas))   $q->where('mes', $mes);
            return;
        }

        // Solapamiento de fechas (campañas).
        if ($periodo === 'overlap') {
            if ($anio !== null) {
                $ini = sprintf('%04d-%02d-01', $anio, $mes ?: 1);
                $fin = $mes !== null
                    ? date('Y-m-t', strtotime($ini))
                    : sprintf('%04d-12-31', $anio);
                $q->whereRaw('fecha_inicio <= ? AND fecha_fin >= ?', [$fin, $ini]);
            }
            return;
        }

        // Período por una columna de fecha (YEAR/MONTH).
        if (is_array($periodo) && $periodo[0] === 'fecha') {
            $col = $periodo[1];
            if ($anio !== null) $q->whereRaw('YEAR(`' . $col . '`) = ?', [$anio]);
            if ($mes !== null)  $q->whereRaw('MONTH(`' . $col . '`) = ?', [$mes]);
            return;
        }

        // Columna que ya es un año (ej. anio_inicio_techo).
        if (is_array($periodo) && $periodo[0] === 'anio_col') {
            $col = $periodo[1];
            if ($anio !== null) $q->where($col, $anio);
            return;
        }

        // Últimos 6 meses desde hoy (mesas activas).
        if (is_array($periodo) && $periodo[0] === 'ultimos_6m') {
            $col = $periodo[1];
            $q->whereRaw('`' . $col . '` >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)');
        }
    }
}
