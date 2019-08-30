<?php

namespace App\Search\filters\inscripciones;

use App\Search\filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class HotFilter implements Filter
{
    public static function apply(Builder $builder, $value)
    {
        $filter = $value;
        $palabras = explode(' ', $filter);
        
        foreach ($palabras as $palabra) {              
            $builder->whereRaw("concat(' ', Persona.nombres, ' ', Persona.apellidoPaterno, ' ', Persona.mail, ' ', Persona.dni) like '%" . $palabra . "%'");
        }
        if (strtolower($filter) === 'pagado') {
            $builder->orWhere('Inscripcion.pago', 1);
        }
        if (strtolower($filter) === 'pendiente') {
            $builder->orWhere('Inscripcion.pago', 0);
            $builder->orWhereNull('Inscripcion.pago');
        }
        if (strtolower($filter) === 'presente') {
            $builder->orWhere('Inscripcion.presente', 1);
        }
        if (strtolower($filter) === 'ausente') {
            $builder->orWhere('Inscripcion.presente', 0);
            $builder->orWhereNull('Inscripcion.presente');
        }

        return $builder;
    }
}