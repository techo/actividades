<?php

namespace App\Search\filters;


use Illuminate\Database\Eloquent\Builder;

class Comunidad implements Filter
{
    public static function apply(Builder $builder, $value)
    {
        $palabras = explode(' ', $value);

    	foreach ($palabras as $palabra) {
    		$builder->whereRaw("concat(' ', Comunidad.nombre) like ?", ['%' . $palabra . '%']);
		}

        return $builder;
    }
}