<?php

namespace App\Search\filters;


use Illuminate\Database\Eloquent\Builder;

class Tipo implements Filter
{
    public static function apply(Builder $builder, $value)
    {
       return $builder->where('Tipo.nombre', 'like', '%' . $value . '%');
    }
}