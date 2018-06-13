<?php
namespace App\Search\filters\inscripciones;

use App\Search\filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class Inscriptos implements Filter
{
    public static function apply(Builder $builder, $value)
    {
        $filter = $value;
        return $builder->where(function ($query) use ($filter) {
            $query->orWhere('Persona.nombres', 'like', '%' . $filter . '%');
            $query->orWhere('Persona.apellidoPaterno', 'like', '%' . $filter . '%');
            $query->orWhere('Persona.dni', 'like', '%' . $filter . '%');
            $query->orWhere('Persona.mail', 'like', '%' . $filter . '%');
        });
    }
}