<?php

return array(
    'dsn' => env('SENTRY_LARAVEL_DSN'),

    // capture release as git sha
    'release' => trim(exec('git log --pretty="%h" -n1 HEAD')),

    // Capture bindings on SQL queries. Por defecto false: los valores bindeados
    // pueden contener PII / tokens / hashes y no deben egresar a un tercero.
    'breadcrumbs.sql_bindings' => env('SENTRY_SQL_BINDINGS', false),

    // Capture default user context. Por defecto false para no enviar datos del usuario.
    'user_context' => env('SENTRY_USER_CONTEXT', false),
);
