<?php


namespace App\Search\filters\inscripciones;

use App\Search\filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class Nombre implements Filter
{
    public static function apply(Builder $builder, $value)
    {
        $condicion = $value['condicion'];
        $valor = $value['valor'];

        $condicion = convertirCondicion($condicion, $valor);

        if (in_array($condicion['comparacion'], ['like', '<', '<=', '>', '>=', '=', '<>']))
        {
            $builder->where('Persona.nombres', $condicion['comparacion'], $condicion['valor']);
        }

        if ($condicion['comparacion'] == 'in')
        {
           $builder->whereIn('Persona.nombres', $condicion['valor']);
        }
        return $builder;
    }
}