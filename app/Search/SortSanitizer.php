<?php

namespace App\Search;

use Illuminate\Support\Facades\Schema;

/**
 * Valida y normaliza una cláusula de ordenamiento recibida desde el request antes de
 * pasarla a orderByRaw(), evitando inyección SQL en ORDER BY (subqueries booleanas /
 * time-based sobre `$request->sort`).
 *
 * Solo se admite un identificador de columna (opcionalmente `tabla.columna`) seguido de
 * una dirección opcional `asc`/`desc`. Cualquier otra cosa —paréntesis, comas, comillas,
 * espacios internos, palabras clave SQL— descarta el valor y se usa el fallback seguro
 * definido por cada clase Search.
 *
 * Además, si se pasa $table, se verifica que la columna EXISTA en esa tabla. El datatable
 * puede pedir orden por una columna de display que no existe físicamente (p.ej. "nombre"
 * en Integrantes o "comunidades") → sin este chequeo, orderByRaw tira 500
 * "Unknown column ... in 'order clause'". El chequeo solo aplica a columnas simples
 * (sin `tabla.` explícita, que es el caso de los listados `select *`).
 */
class SortSanitizer
{
    /**
     * @param  string|null $sort     Valor recibido (p.ej. "nombres asc").
     * @param  string      $fallback Ordenamiento por defecto seguro de la clase Search.
     * @param  string|null $table    Tabla contra la cual validar que la columna exista.
     * @return string                Cláusula segura para orderByRaw().
     */
    public static function sanitize($sort, $fallback = 'created_at desc', $table = null)
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

        // La columna debe existir en la tabla. Solo validamos identificadores simples:
        // un `tabla.columna` puede referir a un join y ya pasó el filtro anti-inyección.
        if ($table !== null && strpos($column, '.') === false && !Schema::hasColumn($table, $column)) {
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
