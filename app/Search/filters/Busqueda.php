<?php

namespace App\Search\filters;

use Illuminate\Database\Eloquent\Builder;

class Busqueda implements Filter
{
    public static function apply(Builder $builder, $value)
    {
        if($value == 'punto'){
            return $builder->join('atl_pais', 'PuntoEncuentro.idPais', '=', 'atl_pais.id')
                ->join('atl_provincias', 'PuntoEncuentro.idProvincia', '=', 'atl_provincias.id')
                ->join('atl_localidades', 'PuntoEncuentro.idLocalidad', '=', 'atl_localidades.id');
        }

        //por lugar de actividad
        return $builder->join('atl_pais', 'Actividad.idPais', '=', 'atl_pais.id')
                ->join('atl_provincias', 'Actividad.idProvincia', '=', 'atl_provincias.id')
                ->join('atl_localidades', 'Actividad.idLocalidad', '=', 'atl_localidades.id');

    }
}