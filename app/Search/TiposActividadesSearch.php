<?php

namespace App\Search;

use App\Actividad;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class TiposActividadesSearch
{
    public static function apply(Request $filters)
    {
        $query = static::applyDecoratorsFromRequest($filters, TiposActividadesSearch::newQuery());
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
        $query = (new Actividad)->newQuery();
        $query->join('Tipo', 'Actividad.idTipo', '=', 'Tipo.idTipo')
            ->leftJoin('PuntoEncuentro', 'Actividad.idActividad', '=', 'PuntoEncuentro.idActividad')
            ->orderBy('Tipo.nombre', 'asc')
            ->selectRaw('distinct Tipo.idTipo, Tipo.nombre')
            ->where('estadoConstruccion', 'Abierta')
            ->where('inscripcionInterna', 0) //Visibilidad pública
            ->whereDate('fechaInicioInscripciones', '<=', date('Y-m-d'))
            ->whereDate('fechaFinInscripciones', '>=', date('Y-m-d'))
            ->whereDate('fechaInicio', '>=', date('Y-m-d'));

        if(config('app.pais')) {
            $query->where('Actividad.idPais', config('app.pais'));
        }
        
        return $query;
    }
}