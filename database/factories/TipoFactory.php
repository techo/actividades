<?php

use Faker\Generator as Faker;

$factory->define(App\Tipo::class, function (Faker $faker) {

    return [
        'nombre' => $faker->name,
		'hs' => 0,
		'fyv' => 0,
		'idCategoria' => factory(\App\CategoriaActividad::class)->create(),
    ];

});