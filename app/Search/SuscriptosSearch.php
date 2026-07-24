<?php

namespace App\Search;


use App\Services\Listados\Filtros\FiltroGenerico;
use App\Suscribe;
use Illuminate\Database\Eloquent\Builder;

class SuscriptosSearch
{
    public static function apply($filters, $sort = 'created_at desc', $per_page = 25)
    {
        $query = static::applyDecoratorsFromRequest($filters, SuscriptosSearch::newQuery());
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
            // Las clases hardcodeadas (filters\usuario\*) son escalares: el filtro
            // genérico tiene precedencia para condiciones avanzadas registradas.
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
        return __NAMESPACE__ . '\\filters\\usuario\\' . studly_case($name);
    }
    private static function isValidDecorator($decorator)
    {
        return class_exists($decorator);
    }
    private static function getResults(Builder $query, $sort, $per_page)
    {
        // return $query->get();
        $query->orderByRaw(SortSanitizer::sanitize($sort, 'created_at desc'));
        return $query->paginate($per_page);
    }

    private static function newQuery(){
        $query = (new Suscribe())->newQuery();


        // cambiar validacion de seguridad
        $query->where('idPais', '=', auth()->user()->idPaisPermitido);

        return $query;
    }
}