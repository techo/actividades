<?php

use Faker\Generator as Faker;

$factory->define(App\Pais::class, function (Faker $faker) {

    return [
		'nombre' => $faker->name,
		'habilitado' => 1,
	];

});
