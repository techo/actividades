<?php

use Faker\Generator as Faker;

$factory->define(App\Inscripcion::class, function (Faker $faker) {
    return [
			'idActividad' => factory('App\Actividad')->create(),
			'idPersona' => factory('App\Persona')->create(),
			'idPuntoEncuentro' => factory('App\PuntoEncuentro')->create(),

			'rol' => $faker->word,

			'estado' => 'Pre-inscripto',
			'presente' => false,
			'pago' => false,

			'evaluacion' => true,
			'aceptarCompromiso' => true,
    ];
});
