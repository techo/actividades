<?php


namespace App\Search\filters\inscripciones;

use App\Search\filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class IdActividad
{
    public static function apply(Builder $builder, $value, $filtros)
    {
        if (!empty($filtros['tipoActividad'])){
            $tipo['comparacion'] = $filtros['tipoActividad']['condicion'];
            $tipo['valor'] = convertirCondicion($filtros['tipoActividad']['condicion'], $filtros['tipoActividad']['valor']);

            if ($tipo['comparacion'] == 'in'){
                $condicionTipoActividad = " and TipoAnt.nombre in (" . implode(",", array_map('quote', $tipo['valor'])) . ")";
            } else {
                $condicionTipoActividad = " and TipoAnt.nombre " . $tipo['comparacion'] . " '" . $tipo['valor'] ."'";
            }
        } else {
            $condicionTipoActividad = "";
        }

       $builder->where('Inscripcion.idActividad', $value);
       $builder->selectRaw('(select count(*) from Inscripcion insAnt, Actividad ActAnt, Tipo TipoAnt
                   where insAnt.idPersona = Persona.idPersona
                   and insAnt.idActividad = ActAnt.idActividad
                   and ActAnt.idTipo = TipoAnt.idTipo
                   and insAnt.`estado` <> \'Desinscripto\' ' .
                   $condicionTipoActividad .
                   ' and insAnt.`idActividad` <> \''. $value .'\' ) as cantActividades');
       return $builder;
    }
}