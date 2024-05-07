<?php

namespace App\Search\filters;


use Illuminate\Database\Eloquent\Builder;

class Integrante implements Filter
{
    public static function apply(Builder $builder, $value)
    {
        $palabras = explode(' ', $value);

    	foreach ($palabras as $palabra) {
    		$builder->join('Persona', 'Integrantes.idPersona', '=', 'Persona.idPersona')
            ->whereRaw("concat(' ', Persona.nombres,' ', Persona.apellidoPaterno,' ', Persona.dni,' ', rol, ' ', despliegue, ' ', relacion) like '%" . $palabra . "%'");
		}

        return $builder;
    }
}