<?php

namespace App\Search\filters;

use Illuminate\Database\Eloquent\Builder;

class Coordinador implements Filter
{
    public static function apply(Builder $builder, $value)
    {
    	$palabras = explode(' ', $value);

    	foreach ($palabras as $palabra) {
    		// CONCAT_WS ignora NULLs (con concat, un dni o apellido NULL hacía que nunca matchee).
    		$builder->whereRaw("CONCAT_WS(' ', nombres, apellidoPaterno, mail, dni) like ?", ['%' . $palabra . '%']);
		}

        return $builder;
    }
}
