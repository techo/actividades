<?php

namespace App\Services\Listados;

use App\Campaign;

/**
 * Catálogo de campos del listado de Suscriptos de una campaña.
 * context_id = campaign_id. Los datos vienen del modelo Suscribe (tabla
 * Suscripciones). Tercer listado enganchado al módulo genérico: solo aporta su
 * definición, sin duplicar lógica de tabla/filtros/vistas.
 */
class SuscriptosCatalogo implements CatalogoListado
{
    use ColumnasSeguimiento;

    const LIST_KEY = 'suscriptos';

    public function config($contextId): array
    {
        Campaign::findOrFail($contextId);

        return [
            'fijas' => config('datatables.suscriptos.fijas'),
            'grupos' => [
                [
                    'key' => 'datos_generales',
                    'titulo' => 'backend.general_data',
                    'campos' => config('datatables.suscriptos.catalogo.datos_generales'),
                ],
                [
                    'key' => 'seguimiento',
                    'titulo' => 'backend.tracking',
                    'campos' => $this->camposSeguimiento(static::LIST_KEY, $contextId),
                ],
            ],
            'defaults' => config('datatables.suscriptos.defaults'),
        ];
    }

    public function defaultFields($contextId): array
    {
        $porKey = collect(config('datatables.suscriptos.catalogo.datos_generales'))->keyBy('key');

        $defaults = collect(config('datatables.suscriptos.defaults'))
            ->map(function ($key) use ($porKey) {
                return $porKey->get($key);
            })
            ->filter()
            ->values()
            ->all();

        return array_merge(config('datatables.suscriptos.fijas'), $defaults);
    }

    public function filterableFields($contextId): array
    {
        Campaign::findOrFail($contextId);

        $base = [
            'mail'               => ['type' => 'text', 'sql' => 'Suscripciones.mail'],
            'telefono'           => ['type' => 'text', 'sql' => 'Suscripciones.telefono'],
            'dni'                => ['type' => 'text', 'sql' => 'Suscripciones.dni'],
            'instagram'          => ['type' => 'text', 'sql' => 'Suscripciones.instagram'],
            'canal_contacto'     => ['type' => 'text', 'sql' => 'Suscripciones.canal_contacto'],
            'genero'             => ['type' => 'text', 'sql' => 'Suscripciones.genero'],
            'ocupacion_actual'   => ['type' => 'text', 'sql' => 'Suscripciones.ocupacion_actual'],
            'experiencia_previa' => ['type' => 'text', 'sql' => 'Suscripciones.experiencia_previa'],
            'fecha_suscripcion'  => ['type' => 'date', 'sql' => 'Suscripciones.created_at'],
            'convertido'         => ['type' => 'bool', 'sql' => 'Suscripciones.convertido'],
        ];

        return array_merge($base, $this->camposSeguimientoFiltrables(static::LIST_KEY, $contextId));
    }

    public function groupableFields($contextId): array
    {
        return array_merge(
            ['canal_contacto', 'genero', 'ocupacion_actual', 'experiencia_previa', 'convertido'],
            $this->camposSeguimientoAgrupables(static::LIST_KEY, $contextId)
        );
    }

    public function defaultViews($contextId): array
    {
        return [
            [
                'nombre' => 'backend.vista_todos',
                'color' => '#1f6feb',
                'config' => ['filtros' => [], 'group_by' => null],
            ],
            [
                'nombre' => 'backend.vista_convertidos',
                'color' => '#2ea043',
                'config' => [
                    'filtros' => [['campo' => 'convertido', 'condicion' => '=', 'valor' => 1]],
                    'group_by' => null,
                ],
            ],
        ];
    }
}
