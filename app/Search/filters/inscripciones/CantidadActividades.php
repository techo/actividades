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

        // Misma definición que la columna "participaciones" (EnriquecedorFilas): actividades
        // DISTINTAS donde la persona estuvo presente, sin inscripciones ni actividades borradas.
        // Antes contaba todas las inscripciones (incluidas desinscripciones y ausentes) y
        // daba otro número que la columna (reclamo #14).
        $subquery = "(select count(distinct insAnt.idActividad) from Inscripcion insAnt
                   join Actividad ActAnt on ActAnt.idActividad = insAnt.idActividad
                   join Tipo TipoAnt on TipoAnt.idTipo = ActAnt.idTipo
                   where insAnt.idPersona = Persona.idPersona
                   and insAnt.presente = 1
                   and insAnt.deleted_at is null
                   and ActAnt.deleted_at is null" .
                   $condicionTipoActividad . ")";

        if (in_array($condicion['comparacion'], ['like', '<', '<=', '>', '>=', '=', '<>']))
        {
            $builder->whereRaw($subquery . " " . $condicion['comparacion'] . " ? ", [$condicion['valor']]);
        }

        if ($condicion['comparacion'] == 'in')
        {
            $valores = array_map('intval', (array) $condicion['valor']);
            $builder->whereRaw($subquery . " in (" . implode(",", $valores ?: [-1]) . ")");
        }
        return $builder;
    }
}