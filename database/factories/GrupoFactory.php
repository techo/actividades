<?php

use Faker\Generator as Faker;

$factory->define(App\Grupo::class, function (Faker $faker) {
    return [
			'idActividad' => factory('App\Actividad')->create(),
			'idPadre' => 0,
			'nombre' => $faker->word,
    ];
});
