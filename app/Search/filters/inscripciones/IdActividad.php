<?php


namespace App\Search\filters\inscripciones;

use App\Search\filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class IdActividad implements Filter
{
    public static function apply(Builder $builder, $value)
    {
       $builder->where('Inscripcion.idActividad', $value);
       $builder->selectRaw('(select count(*) from Inscripcion insAnt
                   where insAnt.idPersona = Persona.idPersona
                   and insAnt.`estado` <> \'Desinscripto\'
                   and insAnt.`idActividad` <> \''. $value .'\' ) as cantActividades');
       return $builder;
    }
}