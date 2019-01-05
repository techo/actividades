<?php

use Faker\Generator as Faker;
use Carbon\Carbon;

$factory->define(App\Pais::class, function (Faker $faker) {

    return [
		'nombre' => $faker->country
	];

});