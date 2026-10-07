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

    'failed'   => 'Credenciais informadas não correspondem com nossos registros.',
    'throttle' => 'Você realizou muitas tentativas de login. Por favor, tente novamente em :seconds segundos.',

    // Vuelta del login social (Google/Facebook), ver LoginController@callbackFromProvider.
    'social_cancelado' => 'O login não foi concluído. Você pode tentar novamente quando quiser.',
    'social_sesion_expirada' => 'Sua sessão de login expirou. Por favor, entre novamente.',
    'social_email_no_verificado' => 'O e-mail da sua conta do Google não está verificado.',
];
