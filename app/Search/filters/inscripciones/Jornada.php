<?php


namespace App\Search\filters\inscripciones;

use App\Search\filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class Jornada implements Filter
{
    public static function apply(Builder $builder, $value)
    {
        $condicion['comparacion'] = $value['condicion'];
        $condicion['valor'] = convertirCondicion($value['condicion'], $value['valor']);

        if (in_array($condicion['comparacion'], ['like', '<', '<=', '>', '>=', '=', '<>']))
        {
            $builder->where('Jornada.nombre', $condicion['comparacion'], $condicion['valor']);
        }

        if ($condicion['comparacion'] == 'in')
        {
            $builder->whereIn('Jornada.nombre', $condicion['valor']);
        }

        return $builder;
    }
}