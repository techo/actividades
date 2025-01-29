<?php

namespace App\Search\filters;

use Illuminate\Database\Eloquent\Builder;

class Tipos implements Filter
{
    public static function apply(Builder $builder, $value)
    {
        if( !is_null($value) && !empty($value)) {
            
            // Extraer el JSON del primer índice del array
            $jsonString = $value[0] ?? null;

            if ($jsonString) {
                // Decodificar el JSON a un array asociativo
                $decodedArray = json_decode($jsonString, true);

                // Extraer solo los valores de idTipo
                $idTipos = array_column($decodedArray, 'idTipo');

                // Aplicar la consulta con whereIn
                return $builder->whereIn('Tipo.idTipo', $idTipos);
            } else {
                return $builder->whereIn('Tipo.idTipo', $value);
            }
        }

        return $builder;
    }
}