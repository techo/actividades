<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used during authentication for various
    | messages that we need to display to the user. You are free to modify
    | these language lines according to your application's requirements.
    |
    */

    'failed' => 'These credentials do not match our records.',
    'throttle' => 'Too many login attempts. Please try again in :seconds seconds.',

    // Vuelta del login social (Google/Facebook), ver LoginController@callbackFromProvider.
    'social_cancelado' => 'Sign-in was not completed. You can try again whenever you like.',
    'social_sesion_expirada' => 'Your sign-in session expired. Please sign in again.',
    'social_email_no_verificado' => 'The email of your Google account is not verified.',
];
