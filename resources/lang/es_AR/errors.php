<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Pantallas de error
    |--------------------------------------------------------------------------
    | Tono TECHO: humano, tranquilizador y con salida clara. La persona que ve
    | esto suele estar frustrada; el objetivo es que se sienta acompañada.
    */

    'back_home' => 'Volver al inicio',
    'code'      => 'Error :n',

    'e500' => [
        'title'       => 'Algo salió mal',
        'message'     => 'Tuvimos un problema de nuestro lado y no pudimos completar lo que estabas haciendo. Ya quedó registrado y lo estamos viendo. Probá de nuevo en un momento.',
        'report'      => 'Reportar este problema',
        'report_hint' => 'Como coordinás/administrás el sistema, podés reportarlo para que el equipo lo revise.',
    ],

    'e404' => [
        'title'   => 'No encontramos esta página',
        'message' => 'La página que buscás no existe o se movió de lugar. Revisá la dirección o volvé al inicio.',
    ],

    'evaluar' => [
        'no_presente' => 'No figurás como presente en esta actividad, por eso no podés evaluarla. Si participaste, pedile a quien coordina que te marque como presente. Si tenés más de una cuenta, revisá haber entrado con la que usaste para inscribirte.',
        'no_abierta'  => 'La evaluación de esta actividad todavía no está abierta. Probá de nuevo más adelante.',
    ],

    'e403' => [
        'title'   => 'No tenés acceso a esto',
        'message' => 'Esta sección requiere permisos que tu cuenta no tiene. Si creés que es un error, escribile a la persona que coordina tu equipo.',
    ],

    'e503' => [
        'title'   => 'Estamos en mantenimiento',
        'message' => 'Estamos haciendo una mejora rápida del sistema. Volvé a intentar en unos minutos. ¡Gracias por la paciencia!',
    ],

    'e419' => [
        'title'     => 'Se venció la página',
        'message'   => 'Tu sesión expiró porque la página estuvo abierta demasiado tiempo. No perdiste nada: te llevamos de vuelta para que puedas continuar.',
        'retry'     => 'CONTINUAR',
        'countdown' => 'Te redirigimos en :seconds…',
    ],

];
