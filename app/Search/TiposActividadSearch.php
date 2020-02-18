<?php

namespace App\Search;


use App\Tipo;
use Illuminate\Database\Eloquent\Builder;

class TiposActividadSearch
{
    public static function apply($filters, $sort = 'idTipo desc', $per_page = 25)
    {
        $query = static::applyDecoratorsFromRequest($filters, TiposActividadSearch::newQuery());
        return static::getResults($query, $sort, $per_page);
    }
    private static function applyDecoratorsFromRequest($filters, Builder $query)
    {
        foreach ($filters as $filterName => $value) {
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

        $query = (new Tipo())->newQuery();
        $query->join('atl_CategoriaActividad', 'Tipo.idCategoria', '=', 'atl_CategoriaActividad.id')
        ->selectRaw('Tipo.idTipo as id, Tipo.nombre as nombre, atl_CategoriaActividad.nombre as categoria');
        return $query;
    }
}