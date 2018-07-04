<?php

namespace App\Search;


use App\Persona;
use Illuminate\Database\Eloquent\Builder;

class UsuariosSearch
{
    public static function apply($filters)
    {
        $query = static::applyDecoratorsFromRequest($filters, UsuariosSearch::newQuery());
        return static::getResults($query);
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
    private static function getResults(Builder $query)
    {
        // return $query->get();
        return $query->paginate(10);
    }

    private static function newQuery(){
        $query = (new Persona())->newQuery();
        return $query;
    }
}