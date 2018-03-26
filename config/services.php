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
    'facebook' => [
        'client_id'     => '1926230754063306',
        'client_secret' => '49c41b0aff793aa92a2471d9869b10d5',
        'redirect'      => 'https://actividades.techo.org/auth/facebook/callback',
    ],
    'google' => [
        'client_id'     => '634896039951-v4nbcbvmmtdcak8b3476t4afbviqetup.apps.googleusercontent.com',
        'client_secret' => 'UsGBC1uHV2S0eul2m8yt2xQV',
        'redirect'      => 'https://actividades.techo.org/auth/google/callback',
    ],
];
