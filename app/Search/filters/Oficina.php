<?php

namespace App\Search\filters;


use Illuminate\Database\Eloquent\Builder;

class Oficina implements Filter
{
    public static function apply(Builder $builder, $value)
    {
       return $builder->where('atl_oficinas.nombre', 'like', '%' . $value . '%');
    }
}