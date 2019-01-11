<?php

use Faker\Generator as Faker;
use Carbon\Carbon;

$factory->define(App\Grupo::class, function (Faker $faker) {

    return [
		'nombre' => $faker->company,
		'idPadre' => 0,
		'idActividad' => function () {
            return factory(App\Actividad::class)->create()->idActividad;
        }
    ];
});