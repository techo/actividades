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

        // Agrupado en un closure para que los orWhere de las palabras clave no
        // escapen del resto del WHERE (ej. el scope por idActividad).
        $builder->where(function ($query) use ($filter, $palabras) {
            foreach ($palabras as $palabra) {
                $query->whereRaw(
                    "concat(' ', Persona.nombres, ' ', Persona.apellidoPaterno, ' ', Persona.mail, ' ', Persona.dni) like ?",
                    ['%' . $palabra . '%']
                );
            }
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

        return $builder;
    }
}