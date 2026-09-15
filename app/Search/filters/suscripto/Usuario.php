<?php

namespace App\Search\filters\suscripto;

use App\Search\filters\Filter;
use Illuminate\Database\Eloquent\Builder;

/**
 * Búsqueda libre para el listado de Suscriptos. La tabla `Suscripciones` guarda
 * `nombre`/`apellido` (singular), no `nombres`/`apellidoPaterno` de Persona: reusar
 * filters\usuario\Usuario tiraba "Unknown column 'nombres' in 'where clause'".
 */
class Usuario implements Filter
{
    public static function apply(Builder $builder, $value)
    {
        foreach (explode(' ', $value) as $palabra) {
            $builder->whereRaw(
                "concat(' ', nombre, ' ', apellido, ' ', mail, ' ', dni) like ?",
                ['%' . $palabra . '%']
            );
        }

        return $builder;
    }
}
