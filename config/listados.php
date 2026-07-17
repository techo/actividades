<?php

/*
|--------------------------------------------------------------------------
| Registry de listados con columnas configurables
|--------------------------------------------------------------------------
|
| Cada entrada define un list_key utilizable en los endpoints genéricos
| /admin/ajax/listados/{listKey}/{contextId}/... (ListadoConfigController).
|
| - catalogo:  clase que implementa CatalogoListado y arma los grupos de
|              campos disponibles para el selector de columnas.
| - authorize: regla de acceso contra el context_id de la ruta. Formas:
|                ['policy' => 'ability', 'model' => Clase::class]
|                  → Gate::authorize(ability, [Clase, $contextId])
|                ['roles' => 'admin|coordinador']
|                  → auth()->user()->hasAnyRole(...)
| - record:    cómo validar que un record_id pertenece al contexto, para
|              los valores de columnas personalizadas.
|                model:   clase Eloquent del registro del listado
|                key:     columna PK del registro (record_id)
|                context: columna que referencia al contexto (o null si
|                         el listado no tiene contexto)
*/

return [

    'inscripciones' => [
        'catalogo' => \App\Services\Listados\InscripcionesCatalogo::class,
        'authorize' => ['policy' => 'verInscripciones', 'model' => \App\Inscripcion::class],
        'record' => ['model' => \App\Inscripcion::class, 'key' => 'idInscripcion', 'context' => 'idActividad'],
    ],

    'integrantes' => [
        'catalogo' => \App\Services\Listados\IntegrantesCatalogo::class,
        // Misma regla que las rutas de equipos (routes/web.php).
        'authorize' => ['roles' => 'admin|coordinador'],
        'record' => ['model' => \App\Integrante::class, 'key' => 'idIntegrante', 'context' => 'idEquipo'],
    ],

];
