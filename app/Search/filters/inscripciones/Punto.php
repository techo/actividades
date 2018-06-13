<?php


namespace App\Search\filters\inscripciones;

use App\Search\filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class Punto implements Filter
{
    public static function apply(Builder $builder, $value)
    {
        $condicion['comparacion'] = $value['condicion'];
        $condicion['valor'] = convertirCondicion($value['condicion'], $value['valor']);

        if (in_array($condicion['comparacion'], ['like', '<', '<=', '>', '>=', '=', '<>']))
        {
            $builder->where(function ($query) use ($condicion) {
                $query->orWhere('PuntoEncuentro.punto', $condicion['comparacion'], $condicion['valor']);
                $query->orWhere('atl_pais.nombre', $condicion['comparacion'], $condicion['valor']);
                $query->orWhere('atl_provincias.provincia', $condicion['comparacion'], $condicion['valor']);
                $query->orWhere('atl_localidades.localidad', $condicion['comparacion'], $condicion['valor']);
            });
        }

        if ($condicion['comparacion'] == 'in')
        {
            $builder->where(function ($query) use ($condicion) {
                $query->orWhereIn('PuntoEncuentro.punto', $condicion['valor']);
                $query->orWhereIn('atl_pais.nombre', $condicion['valor']);
                $query->orWhereIn('atl_provincias.provincia', $condicion['valor']);
                $query->orWhereIn('atl_localidades.localidad', $condicion['valor']);
            });
        }
        return $builder;
    }
}