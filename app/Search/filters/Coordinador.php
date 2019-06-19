<?php

namespace App\Search\filters;

use Illuminate\Database\Eloquent\Builder;

class Coordinador implements Filter
{
    public static function apply(Builder $builder, $value)
    {
    	$palabras = explode(' ', $value);

    	foreach ($palabras as $palabra) {
    		$builder->orWhere('nombres', 'LIKE', '%'. $palabra .'%');
		}
		foreach ($palabras as $palabra) {
    		$builder->orWhere('apellidoPaterno', 'LIKE', '%'. $palabra .'%');
		}
		foreach ($palabras as $palabra) {
    		$builder->orWhere('mail', 'LIKE', '%'. $palabra .'%');
		}

        return $builder;
    }
}
