<?php

namespace App\Search\filters;


use Illuminate\Database\Eloquent\Builder;

class Destacada implements Filter
{
    public static function apply(Builder $builder, $value)
    {   
        if ($value) {
            return $builder->whereNotNull('Actividad.imagen_destacada');
        }
    }
}