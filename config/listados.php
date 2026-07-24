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
| - search:    clase *Search (app/Search) que construye el query del listado;
|              usada por los endpoints genéricos de recuento y facets.
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
        'search' => \App\Search\InscripcionesSearch::class,
        'authorize' => ['policy' => 'verInscripciones', 'model' => \App\Inscripcion::class],
        'record' => [
            'model' => \App\Inscripcion::class,
            'key' => 'idInscripcion',
            'context' => 'idActividad',
            // Columna correlacionada para whereExists de filtros custom/pregunta.
            'sql' => 'Inscripcion.idInscripcion',
            'preguntas' => ['table' => 'inscripcion_respuestas', 'fk' => 'inscripcion_id', 'col' => 'respuesta'],
        ],
    ],

    'integrantes' => [
        'catalogo' => \App\Services\Listados\IntegrantesCatalogo::class,
        'search' => \App\Search\IntegrantesSearch::class,
        // Misma regla que las rutas de equipos (routes/web.php).
        'authorize' => ['roles' => 'admin|coordinador'],
        'record' => [
            'model' => \App\Integrante::class,
            'key' => 'idIntegrante',
            'context' => 'idEquipo',
            'sql' => 'Integrantes.idIntegrante',
        ],
    ],

    'suscriptos' => [
        'catalogo' => \App\Services\Listados\SuscriptosCatalogo::class,
        'search' => \App\Search\SuscriptosSearch::class,
        // El ability recibe el campaign_id (context) y valida país (CampanaPolicy).
        'authorize' => ['policy' => 'verSuscriptos', 'model' => \App\Campaign::class],
        'record' => [
            'model' => \App\Suscribe::class,
            'key' => 'id',
            'context' => 'campaign_id',
            'sql' => 'Suscripciones.id',
        ],
    ],

];
