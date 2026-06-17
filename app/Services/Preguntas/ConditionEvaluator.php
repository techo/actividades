<?php

namespace App\Services\Preguntas;

/**
 * Fuente de verdad ÚNICA de "¿esta pregunta debe mostrarse?".
 *
 * Compartida por Actividades y Campañas, y por la validación + el guardado.
 * Su espejo en cliente vive en resources/js/conditionEvaluator.js — ambas
 * implementaciones deben mantener la misma lógica.
 *
 * Reglas v1:
 *   - Sin condiciones  → visible.
 *   - operator 'equals' → visible si la opción elegida en la pregunta padre
 *     coincide (por id estable) con el valor esperado de la condición.
 *   - Si hay varias condiciones, deben cumplirse TODAS (AND). En v1 normalmente
 *     hay una sola; el AND deja la puerta abierta a la evolución.
 *   - Cascada: si la pregunta padre está oculta, la dependiente también.
 *   - Fail-open: si la condición referencia un padre inexistente, se ignora la
 *     condición (la pregunta se muestra) para no perder datos silenciosamente.
 *
 * Las respuestas se reciben como [ pregunta_id => texto_de_la_respuesta ] (el
 * label elegido). El mapeo label → id de opción se hace contra la configuración
 * viva de la pregunta padre, por eso renombrar un label no rompe la condición.
 */
class ConditionEvaluator
{
    /**
     * @param  iterable $preguntas  modelos de pregunta (con condiciones y opciones_detalle)
     * @param  array    $respuestas [ pregunta_id => respuesta(texto) ]
     * @return int[]    ids de preguntas visibles
     */
    public static function visibleIds($preguntas, array $respuestas)
    {
        $byId = self::indexarPorId($preguntas);
        $cache = [];
        $visibles = [];

        foreach ($byId as $id => $pregunta) {
            if (self::esVisible($id, $byId, $respuestas, $cache)) {
                $visibles[] = $id;
            }
        }

        return $visibles;
    }

    /**
     * ¿Es visible la pregunta $id dado el set y las respuestas?
     */
    public static function esVisible($id, array $byId, array $respuestas, array &$cache = [])
    {
        if (array_key_exists($id, $cache)) {
            return $cache[$id];
        }

        // Guard anti-ciclos: marca temporal mientras se resuelve.
        $cache[$id] = true;

        if (!isset($byId[$id])) {
            return $cache[$id] = true; // desconocida → no la ocultamos
        }

        $pregunta = $byId[$id];
        $condiciones = self::condicionesDe($pregunta);

        if (empty($condiciones)) {
            return $cache[$id] = true;
        }

        foreach ($condiciones as $cond) {
            if (!self::condicionSeCumple($cond, $byId, $respuestas, $cache)) {
                return $cache[$id] = false;
            }
        }

        return $cache[$id] = true;
    }

    private static function condicionSeCumple($cond, array $byId, array $respuestas, array &$cache)
    {
        $parentId = self::valor($cond, 'parent_id');
        $operator = self::valor($cond, 'operator') ?: 'equals';
        $esperado = self::valor($cond, 'value');

        // Padre inexistente → fail-open (se ignora la condición).
        if (!$parentId || !isset($byId[$parentId])) {
            return true;
        }

        // Cascada: si el padre está oculto, la condición no se cumple.
        if (!self::esVisible($parentId, $byId, $respuestas, $cache)) {
            return false;
        }

        $respuestaPadre = isset($respuestas[$parentId]) ? $respuestas[$parentId] : null;
        $opcionId = self::opcionIdSeleccionada($byId[$parentId], $respuestaPadre);

        switch ($operator) {
            case 'equals':
            default:
                return $opcionId !== null && (string) $opcionId === (string) $esperado;
        }
    }

    /**
     * Dado el texto de respuesta elegido, resuelve el id estable de la opción.
     */
    public static function opcionIdSeleccionada($pregunta, $respuestaTexto)
    {
        if ($respuestaTexto === null || $respuestaTexto === '') {
            return null;
        }

        foreach (self::opcionesDetalle($pregunta) as $opcion) {
            $label = is_array($opcion) ? (isset($opcion['label']) ? $opcion['label'] : null) : null;
            if ($label !== null && (string) $label === (string) $respuestaTexto) {
                return is_array($opcion) ? (isset($opcion['id']) ? $opcion['id'] : null) : null;
            }
        }

        return null;
    }

    // ── Helpers de acceso tolerantes a array u objeto/modelo ────────────────

    private static function indexarPorId($preguntas)
    {
        $byId = [];
        foreach ($preguntas as $p) {
            $id = self::valor($p, 'id');
            if ($id !== null) {
                $byId[$id] = $p;
            }
        }
        return $byId;
    }

    private static function condicionesDe($pregunta)
    {
        $cond = self::valor($pregunta, 'condiciones');
        if ($cond === null) {
            return [];
        }
        if (is_object($cond) && method_exists($cond, 'all')) {
            return $cond->all();
        }
        return is_array($cond) ? $cond : [];
    }

    private static function opcionesDetalle($pregunta)
    {
        $det = self::valor($pregunta, 'opciones_detalle');
        if ($det === null) {
            return [];
        }
        if (is_object($det) && method_exists($det, 'toArray')) {
            return $det->toArray();
        }
        return is_array($det) ? $det : [];
    }

    private static function valor($item, $key)
    {
        if (is_array($item)) {
            return array_key_exists($key, $item) ? $item[$key] : null;
        }
        if (is_object($item)) {
            return isset($item->{$key}) ? $item->{$key} : null;
        }
        return null;
    }
}
