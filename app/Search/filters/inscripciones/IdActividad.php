<?php


namespace App\Search\filters\inscripciones;

use App\Search\filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class IdActividad implements Filter
{
    public static function apply(Builder $builder, $value)
    {
       return $builder->where('Inscripcion.idActividad', $value);
    }
}