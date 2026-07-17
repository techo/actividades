<?php

namespace App\Services\Listados;

/**
 * Catálogo de campos de un listado con columnas configurables.
 *
 * Cada implementación define, para su list_key (ver config/listados.php):
 *  - los campos fijos (siempre visibles, fuera del selector),
 *  - los grupos de campos opcionales (con su fieldDef de vuetable),
 *  - las keys visibles por defecto cuando el usuario no guardó preferencias.
 *
 * Convención de fieldDef: el array que consume vuetable-2 (name, title,
 * sortField, callback...) más una `key` estable que identifica al campo en el
 * selector y en las preferencias persistidas. Las columnas de seguimiento usan
 * key "custom_{id}" y las preguntas de inscripción "pregunta_{id}".
 */
interface CatalogoListado
{
    /**
     * Configuración completa para el panel selector de columnas.
     *
     * @return array{fijas: array, grupos: array, defaults: array}
     *   grupos: [['key' => ..., 'titulo' => ..., 'campos' => [fieldDef, ...]], ...]
     */
    public function config($contextId): array;

    /**
     * Campos para el primer render del listado (fijas + defaults),
     * equivalentes a lo que el Blade siempre pasó al componente Vue.
     */
    public function defaultFields($contextId): array;
}
