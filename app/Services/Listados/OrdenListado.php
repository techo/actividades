<?php

namespace App\Services\Listados;

use App\ListadoPreferencia;

/**
 * Recuerda el orden que cada usuario eligió en un listado (columna `orden` de
 * listado_preferencias, mismo formato que el `sort` del datatable: "campo|dir,...").
 *
 * Se guarda en cada carga de datos que trae `sort` (solo si cambió) y se usa en el
 * primer render. Si el usuario nunca ordenó en ese contexto (ej. una actividad
 * nueva), se toma el último orden que usó en el mismo listado.
 */
class OrdenListado
{
    public static function guardar($personaId, string $listKey, $contextId, $sort): void
    {
        $sort = is_string($sort) ? trim($sort) : '';
        if (!$personaId || $sort === '' || strlen($sort) > 255) {
            return;
        }

        $preferencia = ListadoPreferencia::firstOrNew([
            'persona_id' => $personaId,
            'list_key' => $listKey,
            'context_id' => (int) $contextId,
        ]);

        if ($preferencia->orden === $sort) {
            return;
        }

        $preferencia->orden = $sort;
        $preferencia->save();
    }

    /**
     * sortOrder para vuetable ([{field, sortField, direction}]) o $default si el usuario
     * no tiene orden guardado. `field` es el `name` de la columna con ese sortField (lo
     * usa vuetable para la flecha del encabezado); $campos son los fields del listado.
     */
    public static function sortOrderInicial($personaId, string $listKey, $contextId, array $default, array $campos): array
    {
        $orden = ListadoPreferencia::where('persona_id', $personaId)
            ->where('list_key', $listKey)
            ->where('context_id', (int) $contextId)
            ->value('orden');

        if (!$orden) {
            $orden = ListadoPreferencia::where('persona_id', $personaId)
                ->where('list_key', $listKey)
                ->whereNotNull('orden')
                ->orderBy('updated_at', 'desc')
                ->value('orden');
        }

        if (!$orden) {
            return $default;
        }

        $nombres = [];
        foreach ($campos as $campo) {
            if (!empty($campo['sortField']) && !empty($campo['name'])) {
                $nombres[$campo['sortField']] = $campo['name'];
            }
        }

        $sortOrder = [];
        foreach (explode(',', $orden) as $par) {
            $partes = explode('|', $par);
            $sortField = trim($partes[0] ?? '');
            if ($sortField === '') {
                continue;
            }
            $sortOrder[] = [
                'field' => $nombres[$sortField] ?? $sortField,
                'sortField' => $sortField,
                'direction' => strtolower(trim($partes[1] ?? 'asc')) === 'desc' ? 'desc' : 'asc',
            ];
        }

        return $sortOrder ?: $default;
    }
}
