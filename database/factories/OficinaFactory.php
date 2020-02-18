<?php

use Faker\Generator as Faker;

$factory->define(App\Oficina::class, function (Faker $faker) {
    return [
		'nombre' => $faker->name,
    	'id_pais' => factory('App\Pais')->create(),
	];
});
