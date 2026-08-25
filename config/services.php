<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, SparkPost and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
    ],

    'ses' => [
        'key' => env('SES_KEY'),
        'secret' => env('SES_SECRET'),
        'region' => 'us-east-1',
    ],

    'sparkpost' => [
        'secret' => env('SPARKPOST_SECRET'),
    ],

    'stripe' => [
        'model' => App\User::class,
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
    ],

    // Stripe account used for generic donations (independent from per-country
    // enrollment payments which read their keys from atl_pais.config_pago).
    'stripe_donations' => [
        'secret'         => env('STRIPE_DONATIONS_SECRET'),
        'webhook_secret' => env('STRIPE_DONATIONS_WEBHOOK_SECRET'),
        // Publishable key (pk_) entregada a la app para inicializar el SDK.
        // Es pública por diseño; el server no la consume, solo la relaya.
        'public'         => env('STRIPE_DONATIONS_PUBLIC'),
    ],
    'facebook' => [
        'client_id'     => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        'redirect'      => env('FACEBOOK_REDIRECT_URL'),
    ],
    'google' => [
        'client_id'     => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect'      => env('GOOGLE_REDIRECT_URL'),
    ],

    'apple' => [
        'client_id' => env('APPLE_CLIENT_ID'),
    ],

    'onesignal' => [
        // En producción se usa ONESIGNAL_APP_ID.
        // En cualquier otro ambiente (local, staging) se usa ONESIGNAL_APP_ID_DEV
        // si está definido, o cae en ONESIGNAL_APP_ID como fallback.
        // Esto evita que notificaciones de dev lleguen a dispositivos reales.
        'app_id'  => env('APP_ENV') === 'production'
            ? env('ONESIGNAL_APP_ID')
            : env('ONESIGNAL_APP_ID_DEV', env('ONESIGNAL_APP_ID')),
        'api_key' => env('ONESIGNAL_REST_API_KEY'),
    ],

    // Salesforce — verificación de socio/donante para eximir el pago de
    // inscripciones en Argentina (país 13). OAuth 2.0 Client Credentials flow.
    // Apagado por defecto: sin SALESFORCE_ENABLED=true todo es no-op y nadie
    // queda exento (fail-closed). El gate de país evita afectar otros países.
    'salesforce' => [
        'enabled'       => env('SALESFORCE_ENABLED', false),
        // El flujo Client Credentials DEBE pegarse al My Domain (*.my.salesforce.com):
        // techo.lightning.force.com devuelve 302 y login.salesforce.com responde
        // "invalid_grant: request not supported on this domain" (verificado 2026-08-25).
        // Configurable por si el My Domain cambia.
        'token_url'     => env('SALESFORCE_TOKEN_URL', 'https://techo.my.salesforce.com/services/oauth2/token'),
        'client_id'     => env('SALESFORCE_CLIENT_ID'),
        'client_secret' => env('SALESFORCE_CLIENT_SECRET'),
        'api_version'   => env('SALESFORCE_API_VERSION', 'v61.0'),
        // Timeout HTTP en segundos: acotado para no colgar la inscripción si
        // Salesforce tarda (crítico para el comportamiento fail-closed).
        'timeout'       => (int) env('SALESFORCE_TIMEOUT', 5),
        // País donde aplica la exención por socio (Argentina = 13).
        'socio_pais_id' => (int) env('SALESFORCE_SOCIO_PAIS_ID', 13),
        // TTL de la caché del resultado "es socio", en minutos (default 12 h).
        'cache_ttl'     => (int) env('SALESFORCE_CACHE_TTL', 720),
    ],
];
