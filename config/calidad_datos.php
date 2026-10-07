<?php

/*
|--------------------------------------------------------------------------
| Calidad de datos progresiva
|--------------------------------------------------------------------------
|
| Configuración del sistema que mejora los datos identitarios con el uso
| (ver App\Services\CalidadDatos\CalidadDatosPersona).
*/

return [

    // Microprompt de verificación de datos en el paso 'confirmar' de la
    // inscripción. OFF por defecto: se prende con CALIDAD_DATOS_MICROPROMPT=true.
    // Pensado para poder apagarlo en caliente si resulta molesto para la gente.
    'microprompt' => env('CALIDAD_DATOS_MICROPROMPT', false),

    // Meses que una verificación explícita del usuario se considera vigente: no
    // se le vuelve a pedir que revise sus datos hasta que venza.
    'vigencia_meses' => env('CALIDAD_DATOS_VIGENCIA_MESES', 12),

];
