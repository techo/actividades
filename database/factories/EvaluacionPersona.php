<?php

use Faker\Generator as Faker;

$factory->define(App\EvaluacionPersona::class, function (Faker $faker) {
    return [
    		'idActividad' => factory('App\Actividad')->create(),
			'idEvaluado' => factory('App\Persona')->create(),
			'idEvaluador' => factory('App\Persona')->create(),
			'puntajeSocial' => $faker->randomDigit,
			'puntajeTecnico' => $faker->randomDigit,
			'puntajeGenero' => $faker->randomDigit,
			'comentario' => $faker->sentence,
        //
    ];
});
