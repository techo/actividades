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
    		// CONCAT_WS (no CONCAT): en MySQL, CONCAT con cualquier argumento NULL
    		// devuelve NULL, así que una persona con dni/apellido/nombre NULL (típico
    		// del registro por app sin DNI) quedaba INVISIBLE en toda la búsqueda del
    		// admin, incluso buscándola por su email exacto. CONCAT_WS ignora los NULL.
    		$builder->whereRaw("CONCAT_WS(' ', nombres, apellidoPaterno, mail, dni) like ?", ['%' . $palabra . '%']);
		}

        return $builder;
    }
}
