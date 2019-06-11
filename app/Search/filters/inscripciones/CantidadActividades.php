<?php


namespace App\Search\filters\inscripciones;

use App\Search\filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class CantidadActividades
{
    public static function apply(Builder $builder, $value, $filtros)
    {
        $condicion['comparacion'] = $value['condicion'];
        $condicion['valor'] = convertirCondicion($value['condicion'], $value['valor']);

        if (!empty($filtros['tipoActividad'])){
            $tipo['comparacion'] = $filtros['tipoActividad']['condicion'];
            $tipo['valor'] = convertirCondicion($filtros['tipoActividad']['condicion'], $filtros['tipoActividad']['valor']);

            if ($tipo['comparacion'] == 'in'){
                $condicionTipoActividad = " and TipoAnt.nombre in (" . implode(",", array_map('quote', $tipo['valor']))  . ")";
            } else {
                $condicionTipoActividad = " and TipoAnt.nombre " . $tipo['comparacion'] . " '" . $tipo['valor'] ."'";
            }
        } else {
            $condicionTipoActividad = "";
        }

        if (in_array($condicion['comparacion'], ['like', '<', '<=', '>', '>=', '=', '<>']))
        {
            $builder->whereRaw("(select count(*) from Inscripcion insAnt, Actividad ActAnt, Tipo TipoAnt
                   where insAnt.idPersona = Persona.idPersona
                   and insAnt.idActividad = ActAnt.idActividad
                   and ActAnt.idTipo = TipoAnt.idTipo" .
                    $condicionTipoActividad .
                   " and insAnt.idActividad <> Actividad.idActividad) " .
                $condicion['comparacion'] . " ? ", $condicion['valor']);
        }

        if ($condicion['comparacion'] == 'in')
        {
           $builder->whereRaw("(select count(*) from Inscripcion insAnt, Actividad ActAnt, Tipo TipoAnt
                   where insAnt.idPersona = Persona.idPersona
                   and insAnt.idActividad = ActAnt.idActividad
                   and ActAnt.idTipo = TipoAnt.idTipo" .
                   $condicionTipoActividad .
                   isset($condicionTipoActividad) ? $condicionTipoActividad : "" .
                   " and insAnt.idActividad <> Actividad.idActividad) in (" . implode(",",$condicion['valor']) . ")");
        }
        return $builder;
    }
}