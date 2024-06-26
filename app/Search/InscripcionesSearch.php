<?php

namespace App\Search;


use App\Persona;
use App\Search\filters\inscripciones\CantidadActividades;
use App\Search\filters\inscripciones\IdActividad;
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
                //hack para condicion Tipo de Actividad y Cantidad de actividades
                if(in_array($decorator, array('App\Search\filters\inscripciones\IdActividad', 'App\Search\filters\inscripciones\CantidadActividades'))) {
                    $query = $decorator::apply($query, $value, $filters);
                }  else {
                    $query = $decorator::apply($query, $value);
                }
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
            ->leftJoin('Persona as PersonaModificacion', 'PersonaModificacion.idPersona', '=', 'Inscripcion.idPersonaModificacion')
            ->join('Actividad', 'Inscripcion.idActividad', '=', 'Actividad.idActividad')
            ->leftJoin('PuntoEncuentro', 'PuntoEncuentro.idPuntoEncuentro', '=', 'Inscripcion.idPuntoEncuentro')
            ->leftJoin('atl_pais', 'PuntoEncuentro.idPais', '=', 'atl_pais.id')
            ->leftJoin('atl_provincias', 'PuntoEncuentro.idProvincia', '=', 'atl_provincias.id')
            ->leftJoin('atl_localidades', 'PuntoEncuentro.idLocalidad', '=', 'atl_localidades.id')
            ->leftJoin('atl_pais as personaPais', 'Persona.idPais', '=', 'personaPais.id')
            ->leftJoin('atl_provincias as personaProvincia', 'Persona.idProvincia', '=', 'personaProvincia.id')
            ->leftJoin('atl_localidades as personaLocalidad', 'Persona.idLocalidad', '=', 'personaLocalidad.id')
            ->leftJoin('atl_oficinas as oficinaPersona', 'personaProvincia.idOficina', '=', 'oficinaPersona.id')
            ->leftJoin('ficha_medicas as ficha', 'Persona.idPersona', '=', 'ficha.idPersona')
            ->leftJoin('Grupo_Persona as Rol', function ($join) {
                $join->on('Rol.idPersona', '=', 'Persona.idPersona');
                $join->on('Rol.idActividad', '=', 'Inscripcion.idActividad');
            })
            ->leftJoin('Grupo', 'Rol.idGrupo', '=', 'Grupo.idGrupo')
            ->select(
                [
                    'Inscripcion.idPersona',
                    'Persona.dni',
                    'Persona.nombres',
                    'Persona.apellidoPaterno',
                    'Persona.telefonoMovil',
                    'Persona.mail',
                    'Persona.fechaNacimiento',
                    'Persona.genero',
                    'Persona.photo',
                    'oficinaPersona.nombre as oficina',
                    'ficha.grupo_sanguinieo',
                    'ficha.cobertura_nombre',
                    'ficha.cobertura_numero',
                    'ficha.contacto_nombre',
                    'ficha.contacto_telefono',
                    'ficha.contacto_relacion',
                    'ficha.alergias',
                    'ficha.vacunacion_covid',
                    'ficha.alimentacion',
                    'ficha.archivo_medico',
                    'ficha.documento_frente',
                    'ficha.documento_dorso',
                    'personaPais.nombre as pPais',
                    'personaProvincia.provincia as pProvincia',
                    'personaLocalidad.localidad as pLocalidad',
                    'Inscripcion.fechaInscripcion',
                    'Inscripcion.idInscripcion AS id',
                    'Inscripcion.presente',
                    'Inscripcion.pago',
                    'Inscripcion.voucherUrl',
                    'Inscripcion.confirma',
                    'PuntoEncuentro.punto',
                    'atl_pais.nombre as puntoPais',
                    'atl_provincias.provincia as puntoProvincia',
                    'atl_localidades.localidad as puntoLocalidad',
                    'Actividad.costo',
                    'Actividad.idActividad',
                    'Grupo.nombre as nombreGrupo',
                    'Inscripcion.rol as nombreRol',
                    'Inscripcion.roles_aplicados',
                    'Inscripcion.inscripciones_aplicadas',
                    'Persona.estadoPersona',
                    'PersonaModificacion.mail as modificado_por',
                    'Inscripcion.updated_at as modificado_en'
                ]
            )
            ->whereNull('Inscripcion.deleted_at');
        return $query;
    }
}