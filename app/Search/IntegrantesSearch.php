<?php

namespace App\Search;

use App\Integrante;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

class IntegrantesSearch
{
    public static function apply($filters, $sort = 'created_at desc', $per_page = 25)
    {   
        Log::info($filters);
        $query = static::applyDecoratorsFromRequest($filters, IntegrantesSearch::newQuery());
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
        $query = (new Integrante())->newQuery();

        //$query->where('Equipo.idPais', '=', auth()->user()->idPaisPermitido);

        return $query;
    }
}