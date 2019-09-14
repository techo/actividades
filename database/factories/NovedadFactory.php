<?php

use Faker\Generator as Faker;

$factory->define(App\Novedad::class, function (Faker $faker) {

    return [
        'texto' => $faker->sentence,
		'accion' => $faker->word,
		'link' => $faker->url,
    ];

});