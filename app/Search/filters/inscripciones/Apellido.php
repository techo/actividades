<?php


namespace App\Search\filters\inscripciones;

use App\Search\filters\ConvierteComparaciones;
use App\Search\filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class Apellido implements Filter
{
    use ConvierteComparaciones;

    public static function apply(Builder $builder, $value)
    {
        $condicion = $value['condicion'];
        $valor = $value['valor'];

        $condicion = convertirCondicion($condicion, $valor);

        if (in_array($condicion['comparacion'], ['like', '<', '<=', '>', '>=', '=', '<>']))
        {
            $builder->where('Persona.apellidoPaterno', $condicion['comparacion'], $condicion['valor']);
        }

        if ($condicion['comparacion'] == 'in')
        {
           $builder->whereIn('Persona.apellidoPaterno', $condicion['valor']);
        }
        return $builder;
    }
}