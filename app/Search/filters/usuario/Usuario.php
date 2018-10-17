<?php

namespace App\Search\filters\usuario;

use App\Search\filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class Usuario implements Filter
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
