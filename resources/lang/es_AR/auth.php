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

    'failed' => 'Las credenciales introducidas son incorrectas.',
    'throttle' => 'Demasiados intentos de acceso. Inténtelo de nuevo en :seconds segundos.',

    // Vuelta del login social (Google/Facebook), ver LoginController@callbackFromProvider.
    'social_cancelado' => 'No se completó el ingreso. Podés intentarlo de nuevo cuando quieras.',
    'social_sesion_expirada' => 'Tu sesión de ingreso expiró. Por favor, iniciá sesión nuevamente.',
    'social_email_no_verificado' => 'El email de la cuenta de Google no está verificado.',
];
