<?php

namespace App\Search\filters;


use Illuminate\Database\Eloquent\Builder;

class IdComunidad implements Filter
{
    public static function apply(Builder $builder, $value)
    {
       return $builder->where('idComunidad', $value);
    }
}