<?php


namespace App\Search\filters\inscripciones;

use App\Search\filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class HotFilter implements Filter
{
    public static function apply(Builder $builder, $value)
    {
        $filter = $value;
        return $builder->where(function ($query) use ($filter) {
            $query->orWhere('Persona.nombres', 'like', '%' . $filter . '%');
            $query->orWhere('Persona.apellidoPaterno', 'like', '%' . $filter . '%');
            $query->orWhere('Persona.dni', 'like', '%' . $filter . '%');
            $query->orWhere('Persona.mail', 'like', '%' . $filter . '%');
            if (strtolower($filter) === 'pagado') {
                $query->orWhere('Inscripcion.pago', 1);
            }
            if (strtolower($filter) === 'pendiente') {
                $query->orWhere('Inscripcion.pago', 0);
                $query->orWhereNull('Inscripcion.pago');
            }
            if (strtolower($filter) === 'presente') {
                $query->orWhere('Inscripcion.presente', 1);
            }
            if (strtolower($filter) === 'ausente') {
                $query->orWhere('Inscripcion.presente', 0);
                $query->orWhereNull('Inscripcion.presente');
            }
        });
    }
}