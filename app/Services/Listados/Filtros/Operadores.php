<?php

namespace App\Services\Listados\Filtros;

/**
 * Operadores válidos por tipo de campo, para el constructor de condiciones de
 * los listados configurables. Fuente única para el front (qué operadores
 * ofrecer) y el back (qué operadores aceptar).
 */
class Operadores
{
    /**
     * type => [operadores]. `label` para el front se resuelve en el cliente.
     */
    const POR_TIPO = [
        'text'   => ['like', '=', '<>', 'in'],
        'number' => ['=', '<>', '<', '<=', '>', '>=', 'in'],
        'date'   => ['=', '<', '<=', '>', '>=', 'between'],
        'enum'   => ['=', '<>', 'in'],
        'multi'  => ['contains', 'not_contains'],
        'bool'   => ['='],
        'person' => ['=', 'in'],
    ];

    public static function permitidos(string $type): array
    {
        return self::POR_TIPO[$type] ?? [];
    }

    public static function permitido(string $type, $operador): bool
    {
        return in_array($operador, self::permitidos($type), true);
    }
}
