<?php

namespace App\Search\filters;


use Illuminate\Database\Eloquent\Builder;

class Integrante implements Filter
{
    public static function apply(Builder $builder, $value)
    {
        $palabras = explode(' ', $value);

        // El join a Persona va UNA sola vez: hacerlo dentro del foreach duplicaba el
        // join por cada palabra y rompía la búsqueda por nombre completo con
        // "Not unique table/alias: 'Persona'". Un whereRaw por palabra alcanza.
    	$builder->join('Persona', 'Integrantes.idPersona', '=', 'Persona.idPersona');
    	foreach ($palabras as $palabra) {
    		$builder->whereRaw("concat(' ', Persona.nombres,' ', Persona.apellidoPaterno,' ', Persona.dni,' ', rol, ' ', despliegue, ' ', relacion) like ?", ['%' . $palabra . '%']);
		}

        return $builder;
    }
}