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

    /**
     * Campos por los que se puede FILTRAR, para el constructor de condiciones y
     * el recuento. Mapa key => descriptor:
     *   [
     *     'dni'       => ['type' => 'text', 'sql' => 'Persona.dni'],
     *     'estado'    => ['type' => 'enum', 'sql' => '__estado', 'opciones' => [...]],
     *     'custom_5'  => ['type' => 'estado', 'sql' => '__custom:5', 'opciones' => [...]],
     *     'pregunta_3'=> ['type' => 'text', 'sql' => '__pregunta:3'],
     *   ]
     * `type` ∈ {text|number|enum|multi|bool|date|person}.
     * `sql` es una columna/expresión real o un marcador que FiltroGenerico sabe
     * resolver: __custom:{id}, __pregunta:{id}, __estado, __subquery:{name}, __edad.
     * SOLO campos consultables en SQL: nunca agregados post-paginación
     * (participaciones, evaluacion_general, nivel).
     */
    public function filterableFields($contextId): array;

    /**
     * Subconjunto de keys de filterableFields por las que tiene sentido AGRUPAR
     * (enum/bool/estado/persona/texto de baja cardinalidad). Excluye texto libre
     * de alta cardinalidad (dni, mail) y fechas/números crudos.
     *
     * @return string[]
     */
    public function groupableFields($contextId): array;

    /**
     * Vistas predefinidas del listado (definidas en código, read-only).
     * Cada una: ['nombre' => ..., 'color' => ..., 'config' => [...]].
     * El `config` solo puede referenciar campos de filterableFields/groupableFields.
     *
     * @return array
     */
    public function defaultViews($contextId): array;
}
