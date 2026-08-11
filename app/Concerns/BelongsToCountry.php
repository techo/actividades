<?php

namespace App\Concerns;

use App\Scopes\BelongsToCountryScope;
use Illuminate\Database\Eloquent\Builder;

/**
 * Marca un modelo como país-scopeado: aplica automáticamente BelongsToCountryScope.
 *
 * Por defecto filtra por la columna física `idPais` del modelo. Modelos con otro
 * nombre de columna sobreescriben getCountryColumn(); modelos sin columna directa
 * (p.ej. Inscripcion, cuyo país vive en la actividad) sobreescriben applyCountryScope().
 */
trait BelongsToCountry
{
    public static function bootBelongsToCountry()
    {
        static::addGlobalScope(new BelongsToCountryScope());
    }

    /**
     * Columna física de país del modelo. Override si no es 'idPais'.
     */
    public function getCountryColumn(): string
    {
        return 'idPais';
    }

    /**
     * Cómo restringir la query al país dado. Override en modelos sin columna directa.
     */
    public function applyCountryScope(Builder $builder, int $pais): void
    {
        $builder->where($this->getTable() . '.' . $this->getCountryColumn(), $pais);
    }

    /**
     * Escape hatch: consultar ignorando el scope de país (operaciones cross-país legítimas).
     */
    public function scopeTodosLosPaises(Builder $builder): Builder
    {
        return $builder->withoutGlobalScope(BelongsToCountryScope::class);
    }
}
