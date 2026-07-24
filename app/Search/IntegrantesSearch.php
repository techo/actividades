<?php

namespace App\Search;

use App\Integrante;
use App\Services\Listados\Filtros\FiltroGenerico;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

class IntegrantesSearch
{
    public static function apply($filters, $sort = 'created_at desc', $per_page = 25)
    {
        $query = static::applyDecoratorsFromRequest($filters, IntegrantesSearch::newQuery());
        return static::getResults($query, $sort, $per_page);
    }

    public static function query($filters): Builder
    {
        return static::applyDecoratorsFromRequest($filters, static::newQuery());
    }

    private static function applyDecoratorsFromRequest($filters, Builder $query)
    {
        $meta = $filters['__filterable'] ?? [];
        unset($filters['__filterable']);

        foreach ($filters as $filterName => $value) {
            // En integrantes las clases hardcodeadas globales son escalares (no
            // entienden {condicion, valor}), así que el filtro genérico tiene
            // precedencia para condiciones avanzadas sobre campos registrados.
            if (FiltroGenerico::esCondicion($value) && FiltroGenerico::soporta($filterName, $meta)) {
                $query = FiltroGenerico::apply($query, $filterName, $value, $meta);
                continue;
            }
            $decorator = static::createFilterDecorator($filterName);
            if (static::isValidDecorator($decorator)) {
                $query = $decorator::apply($query, $value);
            }
        }
        return $query;
    }
    private static function createFilterDecorator($name)
    {
        return __NAMESPACE__ . '\\filters\\' . studly_case($name);
    }
    private static function isValidDecorator($decorator)
    {
        return class_exists($decorator);
    }
    private static function getResults(Builder $query, $sort, $per_page)
    {
        $query->orderByRaw($sort);
        return $query->paginate($per_page);
    }

    private static function newQuery(){
        $query = (new Integrante())->newQuery();

        return $query;
    }
}