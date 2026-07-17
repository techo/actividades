<?php

namespace App\Services\Listados;

use App\Equipo;

/**
 * Catálogo de campos del listado de Integrantes de un equipo.
 * context_id = idEquipo. Los campos referencian lo que expone
 * IntegranteResource en el payload del index.
 */
class IntegrantesCatalogo implements CatalogoListado
{
    use ColumnasSeguimiento;

    const LIST_KEY = 'integrantes';

    public function config($contextId): array
    {
        Equipo::findOrFail($contextId);

        return [
            'fijas' => config('datatables.integrantes.fijas'),
            'grupos' => [
                [
                    'key' => 'datos_generales',
                    'titulo' => 'backend.general_data',
                    'campos' => config('datatables.integrantes.catalogo.datos_generales'),
                ],
                [
                    'key' => 'seguimiento',
                    'titulo' => 'backend.tracking',
                    'campos' => $this->camposSeguimiento(static::LIST_KEY, $contextId),
                ],
            ],
            'defaults' => config('datatables.integrantes.defaults'),
        ];
    }

    public function defaultFields($contextId): array
    {
        $porKey = collect(config('datatables.integrantes.catalogo.datos_generales'))->keyBy('key');

        $defaults = collect(config('datatables.integrantes.defaults'))
            ->map(function ($key) use ($porKey) {
                return $porKey->get($key);
            })
            ->filter()
            ->values()
            ->all();

        return array_merge(config('datatables.integrantes.fijas'), $defaults);
    }
}
