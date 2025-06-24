<?php

namespace App\Search\filters;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

class ComunidadIntegrantes implements Filter
{
    public static function apply(Builder $builder, $value)
    {
        Log::info($value);
        return $builder->where('idComunidad', $value);
    }
}