<?php

use Faker\Generator as Faker;
use Carbon\Carbon;

$factory->define(App\CategoriaActividad::class, function (Faker $faker) {

    return [
        'nombre' => $faker->name,
        'descripcion' => $faker->name,
    ];

});