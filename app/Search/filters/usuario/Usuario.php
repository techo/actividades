<?php

namespace App\Search\filters\usuario;

use App\Search\filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class Usuario implements Filter
{
    public static function apply(Builder $builder, $value)
    {
    	$palabras = explode(' ', $value);

    	foreach ($palabras as $palabra) {
    		$builder->whereRaw("concat(' ', nombres, ' ', apellidoPaterno, ' ', mail, ' ', dni) like '%" . $palabra . "%'");
		}

        return $builder;
    }
}
