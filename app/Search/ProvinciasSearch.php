<?php

namespace App\Search;

use App\Provincia;
use Illuminate\Database\Eloquent\Builder;

class ProvinciasSearch
{
    public static function apply($filters, $sort = 'created_at desc', $per_page = 25)
    {
        $query = static::applyDecoratorsFromRequest($filters, ProvinciasSearch::newQuery());
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
        return __NAMESPACE__ . '\\filters\\usuario\\' . studly_case($name);
    }
    private static function isValidDecorator($decorator)
    {
        return class_exists($decorator);
    }
    private static function getResults(Builder $query, $sort, $per_page)
    {
        // return $query->get();
        $query->orderByRaw($sort);
        return $query->paginate($per_page);
    }

    private static function newQuery(){
        $query = (new Provincia())->newQuery();

        $query->where('id_pais', '=', auth()->user()->idPaisPermitido);

        return $query;
    }
}