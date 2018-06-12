<?php
/**
 * Created by PhpStorm.
 * User: johan
 * Date: 12/06/18
 * Time: 12:07
 */

namespace App\Search\filters\grupo;

use App\Search\filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class Grupo implements Filter
{
    public static function apply(Builder $builder, $value)
    {
        $builder->where('Persona.nombres', 'LIKE', '%' . $value . '%')
            ->orWhere('Persona.apellidoPaterno', 'LIKE', '%' . $value . '%')
            ->orWhere('Persona.dni', 'LIKE', '%' . $value . '%');
        return $builder;
    }

}