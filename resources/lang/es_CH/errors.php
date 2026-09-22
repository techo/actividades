<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Pantallas de error
    |--------------------------------------------------------------------------
    | Tono TECHO: humano, tranquilizador y con salida clara. Español neutro
    | (Chile y resto de LatAm).
    */

    'back_home' => 'Volver al inicio',
    'code'      => 'Error :n',

    'e500' => [
        'title'       => 'Algo salió mal',
        'message'     => 'Tuvimos un problema de nuestro lado y no pudimos completar lo que estabas haciendo. Ya quedó registrado y lo estamos revisando. Vuelve a intentarlo en un momento.',
        'report'      => 'Reportar este problema',
        'report_hint' => 'Como coordinas/administras el sistema, puedes reportarlo para que el equipo lo revise.',
    ],

    'e404' => [
        'title'   => 'No encontramos esta página',
        'message' => 'La página que buscas no existe o se movió de lugar. Revisa la dirección o vuelve al inicio.',
    ],

    'e403' => [
        'title'   => 'No tienes acceso a esto',
        'message' => 'Esta sección requiere permisos que tu cuenta no tiene. Si crees que es un error, escríbele a la persona que coordina tu equipo.',
    ],

    'e503' => [
        'title'   => 'Estamos en mantenimiento',
        'message' => 'Estamos haciendo una mejora rápida del sistema. Vuelve a intentar en unos minutos. ¡Gracias por la paciencia!',
    ],

    'e419' => [
        'title'     => 'Se venció la página',
        'message'   => 'Tu sesión expiró porque la página estuvo abierta demasiado tiempo. No perdiste nada: te llevamos de vuelta para que puedas continuar.',
        'retry'     => 'CONTINUAR',
        'countdown' => 'Te redirigimos en :seconds…',
    ],

];
