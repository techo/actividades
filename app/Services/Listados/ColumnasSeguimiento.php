<?php

namespace App\Services\Listados;

use App\ListadoColumna;

/**
 * FieldDefs de las columnas de seguimiento de un listado/contexto, compartido
 * por todos los catálogos: las columnas custom funcionan igual en cualquier
 * listado (celda-seguimiento resuelve el tipo desde columnaMeta).
 */
trait ColumnasSeguimiento
{
    protected function camposSeguimiento(string $listKey, $contextId): array
    {
        return ListadoColumna::where('list_key', $listKey)
            ->where('context_id', $contextId)
            ->orderBy('orden')
            ->orderBy('id')
            ->get()
            ->map(function ($columna) use ($listKey, $contextId) {
                return [
                    'key' => 'custom_' . $columna->id,
                    'name' => '__component:celda-seguimiento',
                    'title' => $columna->nombre,
                    'columnaMeta' => [
                        'id' => $columna->id,
                        'tipo' => $columna->tipo,
                        'opciones' => $columna->opciones,
                        'valueKey' => 'custom_' . $columna->id,
                        'listKey' => $listKey,
                        'contextId' => $contextId,
                    ],
                ];
            })
            ->all();
    }
}
