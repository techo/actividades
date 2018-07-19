<?php

namespace App\Search\filters\mis_actividades;

use Illuminate\Database\Eloquent\Builder;
use App\Search\filters\Filter as FilterInterface;

class Filter implements FilterInterface
{
    public static function apply(Builder $query, $value)
    {
        $query->where(function ($query) use ($value) {

            return $query->orWhere('Actividad.nombreActividad', 'LIKE', '%' . $value . '%')
                ->orWhere('atl_localidades.localidad', 'LIKE', '%' . $value . '%');

        });
        return $query;
    }
}