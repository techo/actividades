<?php


namespace App\Search\filters\mis_actividades;

use App\Search\filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class Sort implements Filter
{
    public static function apply(Builder $builder, $value)
    {
        $sort = explode('|', $value);
        list($sortField, $sortOrder) = $sort;
        return $builder->orderBy($sortField, $sortOrder);
    }
}