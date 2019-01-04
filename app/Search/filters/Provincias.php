<?php

namespace App\Search\filters;

use Illuminate\Database\Eloquent\Builder;

class Provincias implements Filter
{
    public static function apply(Builder $builder, $value)
    {
        if( !is_null($value) && !empty($value)) {
            return $builder->whereIn('atl_provincias.id', $value);
        }

        return $builder;
    }
}