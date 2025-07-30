<?php

namespace App\Search\filters;


use Illuminate\Database\Eloquent\Builder;

class Estado implements Filter
{
    public static function apply(Builder $builder, $value)
    {
       return $builder->where('estado', $value);
    }
}