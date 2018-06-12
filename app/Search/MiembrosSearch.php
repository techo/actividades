<?php

namespace App\Search;


use App\Persona;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class MiembrosSearch
{
    public static function apply($filters)
    {
        $query = static::applyDecoratorsFromRequest($filters, MiembrosSearch::newQuery());
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
        return __NAMESPACE__ . '\\filters\\inscripciones\\' . studly_case($name);
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

        $query = Builder::newQuery();
        $query->join('Grupo_Persona', 'Persona.idPersona', '=', 'Grupo_Persona.idPersona');
            //->where('Grupo_Persona.idActividad', '=', $this->idActividad)
            //->where('Grupo_Persona.idGrupo', '=', $this->idGrupo)
        //$query->join('Grupo_Persona', 'Grupo_Persona.idPadre', '=');
            //Grupo::where('idPadre', '=', $this->idGrupo)

        return $query;
    }
}