<?php

namespace App\Search\filters;


use Illuminate\Database\Eloquent\Builder;

class IdEquipo implements Filter
{
    public static function apply(Builder $builder, $value)
    {
       return $builder->where('idEquipo', $value);
    }
}