<?php

namespace App\Search\filters;


use Illuminate\Database\Eloquent\Builder;

class Pais implements Filter
{
    public static function apply(Builder $builder, $value)
    {
       return $builder->where('Actividad.idPais', $value);
    }
}