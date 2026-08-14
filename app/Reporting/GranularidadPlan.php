<?php

namespace App\Reporting;

/**
 * Lógica de granularidad del Plan de indicadores: qué períodos tiene un año en
 * cada granularidad, qué meses abarca cada período, cómo se etiqueta y si todavía
 * se puede editar (regla: editable mientras el período no haya cerrado — el
 * período en curso sigue editable, solo los ya terminados quedan bloqueados).
 *
 * `periodo` es el índice dentro del año: mensual 1-12, trimestral 1-4, semestral
 * 1-2, anual = null (un solo período).
 */
class GranularidadPlan
{
    const GRANULARIDADES = ['mensual', 'trimestral', 'semestral', 'anual'];

    private const MESES = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];

    public static function valida(string $granularidad): bool
    {
        return in_array($granularidad, self::GRANULARIDADES, true);
    }

    /** Cantidad de períodos que tiene un año en esta granularidad. */
    public static function cantidad(string $granularidad): int
    {
        switch ($granularidad) {
            case 'mensual':    return 12;
            case 'trimestral': return 4;
            case 'semestral':  return 2;
            case 'anual':      return 1;
        }
        return 0;
    }

    /** Índices de período (1..N), o [null] para anual. */
    public static function periodos(string $granularidad): array
    {
        if ($granularidad === 'anual') {
            return [null];
        }
        return range(1, self::cantidad($granularidad));
    }

    public static function periodoValido(string $granularidad, ?int $periodo): bool
    {
        if (!self::valida($granularidad)) {
            return false;
        }
        if ($granularidad === 'anual') {
            return $periodo === null;
        }
        return $periodo !== null && $periodo >= 1 && $periodo <= self::cantidad($granularidad);
    }

    /** Meses (1..12) que abarca el período dentro del año. */
    public static function meses(string $granularidad, ?int $periodo): array
    {
        switch ($granularidad) {
            case 'mensual':    return [$periodo];
            case 'trimestral': return range(($periodo - 1) * 3 + 1, ($periodo - 1) * 3 + 3);
            case 'semestral':  return range(($periodo - 1) * 6 + 1, ($periodo - 1) * 6 + 6);
            case 'anual':      return range(1, 12);
        }
        return [];
    }

    /** Etiqueta corta del período (Ene..Dic, T1..T4, S1..S2, o el año). */
    public static function etiqueta(string $granularidad, ?int $periodo, int $anio): string
    {
        switch ($granularidad) {
            case 'mensual':    return self::MESES[$periodo - 1] ?? (string) $periodo;
            case 'trimestral': return 'T' . $periodo;
            case 'semestral':  return 'S' . $periodo;
            case 'anual':      return (string) $anio;
        }
        return '';
    }

    /**
     * Editable si el período todavía no cerró respecto de hoy: años futuros
     * siempre; el año en curso solo si el último mes del período aún no pasó
     * (el período en curso sigue abierto); años pasados nunca.
     */
    public static function editable(string $granularidad, ?int $periodo, int $anio, int $hoyAnio, int $hoyMes): bool
    {
        if ($anio > $hoyAnio) {
            return true;
        }
        if ($anio < $hoyAnio) {
            return false;
        }
        return max(self::meses($granularidad, $periodo)) >= $hoyMes;
    }
}
