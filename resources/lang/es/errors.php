<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Pantallas de error (fallback genérico en español)
    |--------------------------------------------------------------------------
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

    'evaluar' => [
        'no_presente' => 'No figuras como presente en esta actividad, por eso no puedes evaluarla. Si participaste, pide a quien coordina que te marque como presente. Si tienes más de una cuenta, revisa haber entrado con la que usaste para inscribirte.',
        'no_abierta'  => 'La evaluación de esta actividad todavía no está abierta. Intenta de nuevo más adelante.',
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
