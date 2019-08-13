<?php

use Faker\Generator as Faker;

$factory->define(App\Inscripcion::class, function (Faker $faker) {
    return [
			'idActividad' => factory('App\Actividad')->create(),
			'idPersona' => factory('App\Persona')->create(),
			'idPuntoEncuentro' => factory('App\PuntoEncuentro')->create(),

			'rol' => $faker->word,

			'confirma' => 0,
			'pago' => 0,
			'presente' => 0,

			'evaluacion' => true,
			'aceptarCompromiso' => true,
    ];
});

$factory->state(App\Inscripcion::class, 'presente', [ 'presente' => 1, ]);

$factory->state(App\Inscripcion::class, 'preinscripto', [ 'estado' => 'Pre-inscripto', ]);

$factory->state(App\Inscripcion::class, 'pago', [ 'pago' => 1, ]);