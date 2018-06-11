<?php


namespace App\Search\filters\inscripciones;

use App\Search\filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class CantidadActividades implements Filter
{
    public static function apply(Builder $builder, $value)
    {
        $condicion['comparacion'] = $value['condicion'];
        $condicion['valor'] = convertirCondicion($value['condicion'], $value['valor']);

        if (in_array($condicion['comparacion'], ['like', '<', '<=', '>', '>=', '=', '<>']))
        {
            $builder->whereRaw("(select count(*) from Inscripcion insAnt
                   where insAnt.idPersona = Persona.idPersona
                   and insAnt.estado <> 'Desinscripto'
                   and insAnt.idActividad <> Actividad.idActividad) " .
                $condicion['comparacion'] . " ? ", $condicion['valor']);
        }

        if ($condicion['comparacion'] == 'in')
        {
           $builder->whereRaw("(select count(*) from Inscripcion insAnt
                   where insAnt.idPersona = Persona.idPersona
                   and insAnt.estado <> 'Desinscripto'
                   and insAnt.idActividad <> Actividad.idActividad) in (" . implode(",",$condicion['valor']) . ")");
        }
        return $builder;
    }
}