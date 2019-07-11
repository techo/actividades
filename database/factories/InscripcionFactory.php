<?php

use Faker\Generator as Faker;

$factory->define(App\Inscripcion::class, function (Faker $faker) {
    return [
			'idActividad' => factory('App\Actividad')->create(),
			'idPersona' => factory('App\Persona')->create(),
			'idPuntoEncuentro' => factory('App\PuntoEncuentro')->create(),

			'rol' => $faker->word,

			'estado' => 'Sin Contactar',
			'presente' => false,
			'pago' => false,

			'evaluacion' => true,
			'aceptarCompromiso' => true,
    ];
});

$factory->state(App\Inscripcion::class, 'presente', [ 'presente' => true, ]);

$factory->state(App\Inscripcion::class, 'preinscripto', [ 'estado' => 'Pre-inscripto', ]);

$factory->state(App\Inscripcion::class, 'pago', [ 'pago' => true, ]);