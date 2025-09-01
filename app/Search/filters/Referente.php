<?php

namespace App\Search\filters;


use Illuminate\Database\Eloquent\Builder;

class Referente implements Filter
{
    public static function apply(Builder $builder, $value)
    {
        $palabras = explode(' ', $value);

    	foreach ($palabras as $palabra) {
    		$builder->whereRaw("nombre like '%" . $palabra . "%'");
		}

        return $builder;
    }
}