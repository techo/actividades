<?php

use Faker\Generator as Faker;

$factory->define(App\Novedad::class, function (Faker $faker) {

    return [
        'texto' => $faker->sentence,
		'link' => $faker->url,
    ];

});