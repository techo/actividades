<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Global Scope de tenant país.
 *
 * Filtra automáticamente los modelos país-scopeados por el `idPaisPermitido` del
 * usuario autenticado, para que el aislamiento entre países no dependa de que cada
 * ruta lo recuerde (ver docs/security-audit-2026.md, Fase 3).
 *
 * Reglas de bypass (críticas):
 *  1. Sin usuario autenticado (comandos Artisan, jobs, colas, seeders, y el propio
 *     flujo de login antes de resolver el usuario) → NO filtra. Esto evita el patrón
 *     de bug de Actividad::boot() (acceder a auth()->user() sin guard) en contextos CLI.
 *  2. Admin global (idPaisPermitido 0/null = "todos los países", ver UserService y
 *     reporting_acceso_usuario) → ve todo.
 *
 * Escape hatch para consultas cross-país legítimas: Model::todosLosPaises() o
 * Model::withoutGlobalScope(BelongsToCountryScope::class).
 */
class BelongsToCountryScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        // (1) Sin usuario autenticado: no filtrar (CLI/jobs/colas/seeders/login).
        if (! auth()->check()) {
            return;
        }

        // (2) Admin global (idPaisPermitido vacío): ve todo.
        $pais = auth()->user()->idPaisPermitido;
        if (empty($pais)) {
            return;
        }

        // (3) Restringir por país. El "cómo" lo define el modelo (columna directa o relación).
        $model->applyCountryScope($builder, (int) $pais);
    }
}
