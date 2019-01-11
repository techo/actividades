<?php

use Faker\Generator as Faker;
use Carbon\Carbon;

$factory->define(App\EvaluacionActividad::class, function (Faker $faker) {

    return [
		'idPersona' => factory(\App\Persona::class)->create()->idPersona,
		'idActividad' => factory(\App\Actividad::class)->create()->idActividad,
		'puntaje' => rand(1, 10),
		'comentario' => $faker->paragraph
    ];
});