<?php

namespace App\Search\filters\mis_actividades;

use Illuminate\Database\Eloquent\Builder;
use App\Search\filters\Filter as FilterInterface;
use Illuminate\Support\Carbon;

class Date implements FilterInterface
{
    public static function apply(Builder $query, $value)
    {
        $hoy = Carbon::now()->format('y-m-d H-i');
        if ($value === 'pasadas') {
            return $query->where('Actividad.fechaInicio', '<', $hoy);
        }

        return $query->where('Actividad.fechaInicio', '>=', $hoy);
    }
}