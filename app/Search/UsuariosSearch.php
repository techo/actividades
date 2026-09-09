<?php

namespace App\Search;


use App\Persona;
use App\Scopes\BelongsToCountryScope;
use Illuminate\Database\Eloquent\Builder;

class UsuariosSearch
{
    public static function apply($filters, $sort = 'idPersona desc', $per_page = 25, $crossPais = false)
    {
        $query = static::applyDecoratorsFromRequest($filters, UsuariosSearch::newQuery($crossPais));
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
        return __NAMESPACE__ . '\\filters\\usuario\\' . \Illuminate\Support\Str::studly($name);
    }
    private static function isValidDecorator($decorator)
    {
        return class_exists($decorator);
    }
    private static function getResults(Builder $query, $sort, $per_page)
    {
        // return $query->get();
        $query->orderByRaw(SortSanitizer::sanitize($sort, 'idPersona desc'));
        return $query->paginate($per_page);
    }

    private static function newQuery($crossPais = false){
        // Rescate por email exacto: se busca en TODA la base ignorando el scope de país,
        // para poder encontrar a quienes se registraron en otro país (típicamente el
        // país por defecto). El permiso para VER/EDITAR el perfil se decide después con
        // Persona::gestionableCrossPais(); acá solo se relaja el hallazgo.
        if ($crossPais) {
            return Persona::withoutGlobalScope(BelongsToCountryScope::class);
        }

        $query = (new Persona())->newQuery();
        $query->where('idPais', '=', auth()->user()->idPaisPermitido);
        return $query;
    }
}