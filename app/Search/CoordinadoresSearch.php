<?php

namespace App\Search;

use App\Persona;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class CoordinadoresSearch
{
    public static function apply(Request $filters)
    {
        $query = static::applyDecoratorsFromRequest($filters, CoordinadoresSearch::newQuery());
        return static::getResults($query);
    }
    private static function applyDecoratorsFromRequest(Request $request, Builder $query)
    {
        foreach ($request->all() as $filterName => $value) {
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
    private static function getResults(Builder $query)
    {
        return $query->get();
    }

    private static function newQuery(){
        $query = (new Persona)->newQuery();
        $query->take(25)
            ->orderBy('apellidoPaterno', 'asc')
            ->orderBy('nombres');

        return $query;
    }
}