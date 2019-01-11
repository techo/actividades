<?php

use Faker\Generator as Faker;
use Carbon\Carbon;

$factory->define(App\EvaluacionPersona::class, function (Faker $faker) {

    return [
		'idEvaluador' => factory(\App\Persona::class)->create()->idPersona,
		'idEvaluado' => factory(\App\Persona::class)->create()->idPersona,
		'idActividad' => factory(\App\Actividad::class)->create()->idActividad,
		'puntajeSocial' => rand(1, 10),
		'puntajeTecnico' => rand(1, 10),
		'comentario' => $faker->paragraph
    ];
});