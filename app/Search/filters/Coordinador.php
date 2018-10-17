<?php

namespace App\Search\filters;

use Illuminate\Database\Eloquent\Builder;

class Coordinador implements Filter
{
    public static function apply(Builder $builder, $value)
    {
        $builder->where('nombres', 'LIKE', '%' . $value . '%')
            ->orWhere('apellidoPaterno', 'LIKE', '%' . $value . '%')
            ->orWhere('dni', 'LIKE', '%' . $value . '%')
            ->orWhere('mail', 'LIKE', '%' . $value . '%');
            return $builder;
    }
}
