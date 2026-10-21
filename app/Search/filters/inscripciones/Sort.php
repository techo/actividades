<?php


namespace App\Search\filters\inscripciones;

use App\Search\filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class Sort implements Filter
{
    /**
     * `sort` viene del datatable como "campo|dir" o, con multi-sort, "a|asc,b|desc".
     * Antes se tomaba solo el primer par (y "asc,b" como dirección → desc).
     * Siempre se desempata por idInscripcion: con empates (misma fecha de inscripción,
     * mismo grupo, presente…) MySQL devolvía otro orden en cada carga/página.
     */
    public static function apply(Builder $builder, $value)
    {
        foreach (explode(',', (string) $value) as $par) {
            $partes = explode('|', $par);
            $campo = trim($partes[0] ?? '');
            if ($campo === '') {
                continue;
            }
            $direccion = strtolower(trim($partes[1] ?? 'asc')) === 'desc' ? 'desc' : 'asc';
            $builder->orderBy($campo, $direccion);
        }

        return $builder->orderBy('Inscripcion.idInscripcion', 'desc');
    }
}
