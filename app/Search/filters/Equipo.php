<?php

namespace App\Search\filters;


use Illuminate\Database\Eloquent\Builder;

class Equipo implements Filter
{
    public static function apply(Builder $builder, $value)
    {
        $palabras = explode(' ', $value);

    	foreach ($palabras as $palabra) {
    		$builder->whereRaw("concat(' ', nombre, ' ', area) like '%" . $palabra . "%'");
		}

        return $builder;
    }
}