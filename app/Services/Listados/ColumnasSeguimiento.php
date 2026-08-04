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
        return $this->columnasSeguimiento($listKey, $contextId)
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

    /**
     * Descriptores filtrables de las columnas de seguimiento del contexto.
     * Mapa key => ['type' => ..., 'sql' => '__custom:{id}', 'opciones' => ...].
     */
    protected function camposSeguimientoFiltrables(string $listKey, $contextId): array
    {
        return $this->columnasSeguimiento($listKey, $contextId)
            ->mapWithKeys(function ($columna) {
                $descriptor = [
                    'type' => static::tipoCustomAFiltro($columna->tipo),
                    'sql' => '__custom:' . $columna->id,
                ];
                if (in_array($columna->tipo, ['estado', 'etiquetas'])) {
                    $descriptor['opciones'] = $columna->opciones;
                }
                return ['custom_' . $columna->id => $descriptor];
            })
            ->all();
    }

    /**
     * Keys de columnas de seguimiento agrupables (todas salvo texto libre y fecha,
     * que producen demasiados buckets).
     *
     * @return string[]
     */
    protected function camposSeguimientoAgrupables(string $listKey, $contextId): array
    {
        return $this->columnasSeguimiento($listKey, $contextId)
            ->filter(function ($columna) {
                return in_array($columna->tipo, ['casilla', 'estado', 'etiquetas', 'persona']);
            })
            ->map(function ($columna) {
                return 'custom_' . $columna->id;
            })
            ->values()
            ->all();
    }

    private function columnasSeguimiento(string $listKey, $contextId)
    {
        return ListadoColumna::where('list_key', $listKey)
            ->where('context_id', $contextId)
            ->orderBy('orden')
            ->orderBy('id')
            ->get();
    }

    /**
     * Traduce el tipo de una columna de seguimiento (ListadoColumna::TIPOS) al
     * type del filtro genérico.
     */
    protected static function tipoCustomAFiltro(string $tipo): string
    {
        switch ($tipo) {
            case 'casilla':   return 'bool';
            case 'estado':    return 'enum';
            case 'etiquetas': return 'multi';
            case 'fecha':     return 'date';
            case 'persona':   return 'person';
            default:          return 'text'; // texto
        }
    }
}
