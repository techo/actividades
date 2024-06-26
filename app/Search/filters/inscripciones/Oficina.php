<?php

namespace App\Search\filters\inscripciones;

use App\Search\filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class Oficina implements Filter
{
    public static function apply(Builder $builder, $value)
    {
       $condicion['comparacion'] = $value['condicion'];
       $condicion['valor'] = convertirCondicion($value['condicion'], $value['valor']);

       if (in_array($condicion['comparacion'], ['like', '<', '<=', '>', '>=', '=', '<>']))
       {
           $builder->where('oficinaPersona.nombre', $condicion['comparacion'], $condicion['valor']);
       }

       if ($condicion['comparacion'] == 'in')
       {
          $builder->whereIn('oficinaPersona.nombre', $condicion['valor']);
       }

       return $builder;
    }
}