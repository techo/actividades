<?php

namespace App\Search;


use App\Persona;
use Illuminate\Database\Eloquent\Builder;

class InscripcionesSearch
{
    public static function apply($filters)
    {
        $query = static::applyDecoratorsFromRequest($filters, InscripcionesSearch::newQuery());
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

        $query = (new Persona)->newQuery();
        $query->join('Inscripcion', 'Persona.idPersona', '=', 'Inscripcion.idPersona')
            ->join('Actividad', 'Inscripcion.idActividad', '=', 'Actividad.idActividad')
            ->join('PuntoEncuentro', 'PuntoEncuentro.idPuntoEncuentro', '=', 'Inscripcion.idPuntoEncuentro')
            ->leftJoin('atl_pais', 'PuntoEncuentro.idPais', '=', 'atl_pais.id')
            ->leftJoin('atl_provincias', 'PuntoEncuentro.idProvincia', '=', 'atl_provincias.id')
            ->leftJoin('atl_localidades', 'PuntoEncuentro.idLocalidad', '=', 'atl_localidades.id')
            ->leftJoin('atl_pais as personaPais', 'Persona.idPais', '=', 'personaPais.id')
            ->leftJoin('atl_provincias as personaProvincia', 'Persona.idProvincia', '=', 'personaProvincia.id')
            ->leftJoin('atl_localidades as personaLocalidad', 'Persona.idLocalidad', '=', 'personaLocalidad.id')
            ->select(
                [
                    'Inscripcion.idPersona',
                    'Persona.dni',
                    'Persona.nombres',
                    'Persona.apellidoPaterno',
                    'Persona.telefonoMovil',
                    'Persona.mail',
                    'Persona.fechaNacimiento',
                    'Persona.sexo',
                    'personaPais.nombre as pPais',
                    'personaProvincia.provincia as pProvincia',
                    'personaLocalidad.localidad as pLocalidad',
                    'Inscripcion.fechaInscripcion',
                    'Inscripcion.idInscripcion AS id',
                    'Inscripcion.presente',
                    'Inscripcion.pago',
                    'Inscripcion.estado',
                    'PuntoEncuentro.punto',
                    'atl_pais.nombre as puntoPais',
                    'atl_provincias.provincia as puntoProvincia',
                    'atl_localidades.localidad as puntoLocalidad',
                    'Actividad.costo',
                    'Actividad.idActividad'
                ]
            )
            ->where('Inscripcion.estado', '<>', 'Desinscripto');
        return $query;
    }
}