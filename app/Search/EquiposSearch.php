<?php

namespace App\Search;

use App\Equipo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

class EquiposSearch
{
    public static function apply($filters, $sort = 'created_at desc', $per_page = 25, $idComunidad = null, $idOficina = null, $forzarPorOficina = false)
    {
        $query = static::applyDecoratorsFromRequest($filters, EquiposSearch::newQuery($idComunidad, $idOficina, $forzarPorOficina));
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
        // El orden por defecto se califica con la tabla base porque la consulta puede
        // hacer join con `coordinadores_equipos` (rama coordinador), que también tiene
        // una columna `created_at` → sin calificar, MySQL tira 1052 (columna ambigua).
        $orden = SortSanitizer::sanitize($sort, 'Equipo.created_at desc');
        $orden = static::calificarColumnaAmbigua($orden);
        $query->orderByRaw($orden);
        return $query->paginate($per_page);
    }

    /**
     * Antepone la tabla base `Equipo` a las columnas de orden que existen en más de una
     * de las tablas del join (hoy: `created_at`). Evita el error 1052 cuando el sort
     * llega sin calificar (default o desde el request).
     */
    private static function calificarColumnaAmbigua($orden)
    {
        return preg_replace('/^(created_at)(\s|$)/', 'Equipo.$1$2', $orden);
    }

    private static function newQuery($idComunidad=null, $idOficina=null, $forzarPorOficina=false){
        $query = (new Equipo())->newQuery();

        if ($idComunidad) {
            $query->join('equipo_comunidad', 'equipo_comunidad.idEquipo', '=', 'Equipo.idEquipo')
                    ->where('equipo_comunidad.idComunidad', $idComunidad);
        }

        if ($idOficina) {
            $query->where('Equipo.idOficina', $idOficina);
        }

        if(auth()->user()->hasRole("admin") || $forzarPorOficina){
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