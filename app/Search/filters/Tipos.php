<?php

namespace App\Search\filters;

use Illuminate\Database\Eloquent\Builder;

class Tipos implements Filter
{
    public static function apply(Builder $builder, $value)
    {
        if (is_null($value) || empty($value)) {
            return $builder;
        }

        // Formato del frontend: el valor (o su primer índice) es un JSON string
        // con la forma [{"idTipo": N}, ...]. La ruta de categorías hace
        // $request->merge(['tipos' => json_encode([['idTipo'=>N], ...])]).
        $first = is_array($value) ? ($value[0] ?? null) : $value;

        if (is_string($first)) {
            $decoded = json_decode($first, true);

            if (is_array($decoded)) {
                $idTipos = array_column($decoded, 'idTipo') ?: $decoded;
                return $builder->whereIn('Tipo.idTipo', $idTipos);
            }

            return $builder;
        }

        // Formato alternativo: array plano de ids [N, ...]
        return $builder->whereIn('Tipo.idTipo', (array) $value);
    }
}