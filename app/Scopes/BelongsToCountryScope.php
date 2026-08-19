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
 *  2. Fuera del backoffice (web pública, ajax del frontend, API móvil) → NO filtra.
 *     El aislamiento tenant es un control del backoffice; el país del frontend lo
 *     resuelve config('app.pais') (middleware SeleccionarPais), no el idPaisPermitido
 *     del usuario. Si se filtrara acá, un admin/coordinador logueado dejaría de ver
 *     las actividades de otros países al navegar el frontend o usar la app.
 *  3. Admin global (idPaisPermitido 0/null = "todos los países", ver UserService y
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

        // (2) El scope de tenant es una defensa del BACKOFFICE. Fuera de /admin
        //     (web pública, ajax del frontend, API móvil) el país se resuelve por
        //     config('app.pais') vía el middleware SeleccionarPais, NO por el
        //     idPaisPermitido del usuario. Aplicarlo ahí le ocultaría a un admin
        //     (o coordinador) logueado las actividades de otros países al navegar
        //     el frontend / usar la app. Ver docs/security-audit-2026.md (Fase 3).
        if (app()->runningInConsole() || ! optional(request())->is('admin', 'admin/*')) {
            return;
        }

        // (3) Admin global (idPaisPermitido vacío): ve todo.
        $pais = auth()->user()->idPaisPermitido;
        if (empty($pais)) {
            return;
        }

        // (4) Restringir por país. El "cómo" lo define el modelo (columna directa o relación).
        $model->applyCountryScope($builder, (int) $pais);
    }
}
