<?php

use Faker\Generator as Faker;
use Carbon\Carbon;

$factory->define(App\CategoriaActividad::class, function (Faker $faker) {

    return [
        'nombre' => $faker->name,
        'descripcion' => $faker->paragraph,
        //'imagen' => 'img/' . $faker->image('public/img', 380, 248, $category = null, $fullPath = false, $randomize = true),
        'imagen' => 'img/Actividades-en-asentamiento.jpg',
        'color' => $faker->hexcolor
    ];

});