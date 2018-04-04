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
        'client_id'     => '146696142692543',
        'client_secret' => '206e76d3afcff91448abd36cd7bd71fe',
        'redirect'      => 'https://actividades.techo.org/auth/facebook/callback',
    ],
    'google' => [
        'client_id'     => '421717671652-on5e9puc5lg0qibghh2dhicnqjbobfrb.apps.googleusercontent.com',
        'client_secret' => 'i4ditSJe5gBdUiWuwQibjaZ4',
        'redirect'      => 'https://actividades.techo.org/auth/google/callback',
    ],
];
