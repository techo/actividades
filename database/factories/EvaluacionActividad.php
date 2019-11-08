<?php

use Faker\Generator as Faker;

$factory->define(App\EvaluacionActividad::class, function (Faker $faker) {
    return [
        'idActividad' => factory('App\Actividad')->create(),
		'idPersona' => factory('App\Persona')->create(),
		'puntaje' => $faker->randomDigit,
		'comentario' => $faker->sentence,
    ];
});
