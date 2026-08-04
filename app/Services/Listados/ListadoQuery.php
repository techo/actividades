<?php

namespace App\Services\Listados;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Orquestador compartido de los listados con columnas configurables.
 *
 * Centraliza la traducción request → $filtros (búsqueda libre + condiciones
 * avanzadas), la metadata de campos filtrables (con el contexto que necesita
 * FiltroGenerico) y la construcción del query vía la clase *Search del registry.
 * Lo usan el index de cada listado y los endpoints genéricos de recuento/facets.
 */
class ListadoQuery
{
    /**
     * Metadata de campos filtrables + el bloque reservado __ctx que FiltroGenerico
     * usa para resolver __custom / __pregunta (correlación whereExists).
     */
    public function metaFiltrable(string $listKey, $contextId): array
    {
        $listado = $this->listado($listKey);
        $catalogo = app($listado['catalogo']);

        $meta = $catalogo->filterableFields($contextId);
        $meta['__ctx'] = [
            'record_sql' => $listado['record']['sql'] ?? null,
            'pregunta' => $listado['record']['preguntas'] ?? null,
        ];

        return $meta;
    }

    /**
     * Construye el array de filtros a partir del request (mismo vocabulario que
     * ya viajaba: `filter` → HotFilter, `condiciones[]` → {campo:{condicion,valor}}),
     * más el contexto base del listado y la metadata filtrable.
     *
     * @param array $base filtros base extra del index (ej. estado de integrantes)
     */
    public function filtrosDesdeRequest(Request $request, string $listKey, $contextId, array $base = []): array
    {
        $listado = $this->listado($listKey);

        // Passthrough de params que consumen los filtros hardcodeados (sort, etc.),
        // salvo los que este servicio traduce explícitamente.
        $filtros = array_merge(
            $request->except(['filter', 'condiciones', 'preview', 'group_by', 'page']),
            [$listado['record']['context'] => $contextId],
            $base
        );

        if ($request->filled('filter')) {
            $filtros['HotFilter'] = $request->input('filter');
        }

        foreach ((array) $request->input('condiciones', []) as $condicion) {
            $condicion = is_string($condicion) ? json_decode($condicion, true) : $condicion;
            if (!is_array($condicion) || !isset($condicion['campo'])) {
                continue;
            }
            $filtros[$condicion['campo']] = [
                'condicion' => $condicion['condicion'] ?? '=',
                'valor' => $condicion['valor'] ?? null,
            ];
        }

        $filtros['__filterable'] = $this->metaFiltrable($listKey, $contextId);

        return $filtros;
    }

    /**
     * Query builder del listado con los filtros ya aplicados.
     */
    public function builder(string $listKey, array $filtros): Builder
    {
        $search = $this->listado($listKey)['search'];

        return $search::query($filtros);
    }

    /**
     * Total de registros que devuelve el listado con los filtros dados.
     */
    public function contar(string $listKey, array $filtros): int
    {
        return $this->builder($listKey, $filtros)->count();
    }

    /**
     * Recuento agregando una condición de preview a los filtros actuales.
     */
    public function contarPreview(string $listKey, array $filtros, array $preview): int
    {
        if (isset($preview['campo'])) {
            $filtros[$preview['campo']] = [
                'condicion' => $preview['condicion'] ?? '=',
                'valor' => $preview['valor'] ?? null,
            ];
        }

        return $this->contar($listKey, $filtros);
    }

    private function listado(string $listKey): array
    {
        $listado = config("listados.$listKey");
        if (!$listado) {
            abort(404);
        }

        return $listado;
    }
}
