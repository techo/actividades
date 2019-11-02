<?php

use Faker\Generator as Faker;

$factory->define(App\Tipo::class, function (Faker $faker) {

    return [
        'nombre' => $faker->name,
		'idCategoria' => factory(\App\CategoriaActividad::class)->create(),
    ];

});