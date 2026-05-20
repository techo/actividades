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
];
