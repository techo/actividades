<?php

namespace App\Search\filters;


use Illuminate\Database\Eloquent\Builder;

class Categoria implements Filter
{
    public static function apply(Builder $builder, $value)
    {
       return $builder->where('Tipo.idCategoria', $value);
    }
}