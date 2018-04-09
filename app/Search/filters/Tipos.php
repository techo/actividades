<?php

namespace App\Search\filters;

use Illuminate\Database\Eloquent\Builder;

class Tipos implements Filter
{
    public static function apply(Builder $builder, $value)
    {
        if( !is_null($value) && !empty($value)) {
            return $builder->whereIn('Tipo.idTipo', $value);
        }

        return $builder;
    }
}