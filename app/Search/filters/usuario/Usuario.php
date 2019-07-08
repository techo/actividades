<?php

namespace App\Search\filters\usuario;

use App\Search\filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class Usuario implements Filter
{
    public static function apply(Builder $builder, $value)
    {
    	$values = explode(' ', $value);

    	foreach ($values as $v)
        	$builder->whereRaw("concat(' ', nombres, apellidoPaterno, mail) like '%" . $v . "%'");

        return $builder;
    }
}
