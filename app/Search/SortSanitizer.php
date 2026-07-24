<?php

namespace App\Search;

/**
 * Valida y normaliza una cláusula de ordenamiento recibida desde el request antes de
 * pasarla a orderByRaw(), evitando inyección SQL en ORDER BY (subqueries booleanas /
 * time-based sobre `$request->sort`).
 *
 * Solo se admite un identificador de columna (opcionalmente `tabla.columna`) seguido de
 * una dirección opcional `asc`/`desc`. Cualquier otra cosa —paréntesis, comas, comillas,
 * espacios internos, palabras clave SQL— descarta el valor y se usa el fallback seguro
 * definido por cada clase Search.
 */
class SortSanitizer
{
    /**
     * @param  string|null $sort     Valor recibido (p.ej. "nombres asc").
     * @param  string      $fallback Ordenamiento por defecto seguro de la clase Search.
     * @return string                Cláusula segura para orderByRaw().
     */
    public static function sanitize($sort, $fallback = 'created_at desc')
    {
        if (!is_string($sort) || trim($sort) === '') {
            return $fallback;
        }

        $tokens = preg_split('/\s+/', trim($sort));

        // A lo sumo: columna + dirección.
        if (count($tokens) > 2) {
            return $fallback;
        }

        $column = $tokens[0];
        if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*(\.[A-Za-z_][A-Za-z0-9_]*)?$/', $column)) {
            return $fallback;
        }

        $direction = 'asc';
        if (isset($tokens[1])) {
            $dir = strtolower($tokens[1]);
            if ($dir !== 'asc' && $dir !== 'desc') {
                return $fallback;
            }
            $direction = $dir;
        }

        return $column . ' ' . $direction;
    }
}
