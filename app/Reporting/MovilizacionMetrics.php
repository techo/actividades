<?php

namespace App\Reporting;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Definición única de las métricas de movilización (fuente de verdad).
 *
 * Lee de la vista reporting_fact_participacion (datamart), que codifica las
 * reglas canónicas UNA sola vez y es la misma fuente que consume Power BI:
 *  - "movilizado" = participaciones con presente=1 (cuenta presencias, NO personas).
 *  - período por la fecha de la actividad (anio = YEAR(Actividad.fechaInicio)).
 *  - KPI = presente=1 AND tipo_indicador IN ('territorio','construccion_de_viviendas').
 *  - excluye inscripciones y actividades soft-deleted (resuelto en la vista).
 *
 * Cualquier consumidor (backoffice, futura API de reporting) usa estos métodos en
 * lugar de reconstruir la lógica, garantizando que app y Power BI no diverjan.
 */
class MovilizacionMetrics
{
    const VISTA = 'reporting_fact_participacion';

    protected static function base($anio, $pais = null, $oficina = null): Builder
    {
        $query = DB::table(static::VISTA)->where('anio', $anio);

        if ($pais)    $query->where('idPais', $pais);
        if ($oficina) $query->where('idOficina', $oficina);

        return $query;
    }

    /** Movilizados (participaciones): cuenta presencias. */
    public static function movilizadosTotal($anio, $pais = null, $oficina = null): int
    {
        return (int) static::base($anio, $pais, $oficina)->where('es_presente', 1)->count();
    }

    /** Personas únicas movilizadas. */
    public static function personasUnicas($anio, $pais = null, $oficina = null): int
    {
        return (int) static::base($anio, $pais, $oficina)
            ->where('es_presente', 1)
            ->distinct()
            ->count('person_key');
    }

    /** Movilizados que cuentan para la meta institucional (territorio + construcción). */
    public static function movilizadosKpi($anio, $pais = null, $oficina = null): int
    {
        return (int) static::base($anio, $pais, $oficina)->where('es_kpi', 1)->count();
    }
}
