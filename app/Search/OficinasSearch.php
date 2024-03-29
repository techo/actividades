<?php

namespace App\Search;


use App\Oficina;
use Illuminate\Database\Eloquent\Builder;

class OficinasSearch
{
    public static function apply($filters, $sort = 'idOficina desc', $per_page = 25)
    {
        $query = static::applyDecoratorsFromRequest($filters, OficinasSearch::newQuery());
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

        $query = (new Oficina())->newQuery();

        // sumar validacion de seguridad
        $query->where('atl_oficinas.id_pais', '=', auth()->user()->idPaisPermitido);
        $query->join('atl_pais', 'atl_oficinas.id_pais', '=', 'atl_pais.id')
        ->selectRaw('atl_oficinas.id as id, atl_oficinas.nombre as oficina, atl_pais.nombre as pais');
        return $query;
    }
}