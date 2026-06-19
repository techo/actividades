<?php

namespace App\Reporting;

use App\Inscripcion;
use Illuminate\Database\Eloquent\Builder;

/**
 * Definición única de las métricas de movilización (fuente de verdad).
 *
 * Reglas canónicas (confirmadas con negocio):
 *  - "movilizado" = participaciones con presente=1 (cuenta presencias, NO personas).
 *  - El período se ubica por la fecha de la actividad (Actividad.fechaInicio).
 *  - "personas únicas" = COUNT(DISTINCT idPersona) sobre esas presencias.
 *
 * Cualquier consumidor (backoffice, futura API de reporting, datamart) debe usar
 * estos métodos en lugar de reconstruir la lógica.
 */
class MovilizacionMetrics
{
    /** Presencias ubicadas por la fecha de la actividad, con filtros opcionales. */
    protected static function basePresentes($anio, $pais = null, $oficina = null): Builder
    {
        $query = Inscripcion::join('Actividad', 'Actividad.idActividad', '=', 'Inscripcion.idActividad')
            ->whereYear('Actividad.fechaInicio', $anio)
            ->where('Inscripcion.presente', 1);

        if ($pais)    $query->where('Actividad.idPais', $pais);
        if ($oficina) $query->where('Actividad.idOficina', $oficina);

        return $query;
    }

    /** Movilizados (participaciones): cuenta presencias. */
    public static function movilizadosTotal($anio, $pais = null, $oficina = null): int
    {
        return static::basePresentes($anio, $pais, $oficina)->count();
    }

    /** Personas únicas movilizadas. */
    public static function personasUnicas($anio, $pais = null, $oficina = null): int
    {
        return (int) static::basePresentes($anio, $pais, $oficina)
            ->distinct()
            ->count('Inscripcion.idPersona');
    }
}
