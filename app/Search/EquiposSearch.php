<?php

namespace App\Search;

use App\Equipo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

class EquiposSearch
{
    public static function apply($filters, $sort = 'created_at desc', $per_page = 25)
    {
        $query = static::applyDecoratorsFromRequest($filters, EquiposSearch::newQuery());
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
        // return $query->get();
        $query->orderByRaw($sort);
        return $query->paginate($per_page);
    }

    private static function newQuery(){
        $query = (new Equipo())->newQuery();        

        if(auth()->user()->hasRole("admin")){
            $query->where('idPais', '=', auth()->user()->idPaisPermitido);
        } else if(auth()->user()->hasRole("coordinador")){
            $query
            ->join('coordinadores_equipos','coordinadores_equipos.idEquipo','=','Equipo.idEquipo')
            ->where('idPersona', auth()->user()->idPersona)
            ->get();
        }

        return $query;
    }
}