<?php

namespace App\Search;


use App\Actividad;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class MisActividadesSearch
{
    public static function apply(Request $filters)
    {
        $query = static::applyDecoratorsFromRequest($filters, MisActividadesSearch::newQuery());
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
        return __NAMESPACE__ . '\\filters\\mis_actividades\\' . studly_case($name);
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

        $query->join('Inscripcion','Inscripcion.idActividad','=','Actividad.idActividad')
            ->join('atl_localidades', 'atl_localidades.id', '=', 'Actividad.idLocalidad')
            ->join('PuntoEncuentro', 'PuntoEncuentro.idPuntoEncuentro', '=', 'Inscripcion.idPuntoEncuentro')
            ->join('Tipo', 'Tipo.idTipo', '=', 'Actividad.idTipo')
            ->where('Inscripcion.idPersona', auth()->user()->idPersona)
            ->whereNotIn('estado',['Desinscripto'])
            ->select(['Actividad.*', 'Inscripcion.presente', 'PuntoEncuentro.punto', 'Tipo.*']);
        return $query;
    }
}