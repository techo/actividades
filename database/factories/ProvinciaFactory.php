<?php

use Faker\Generator as Faker;
use Carbon\Carbon;

$factory->define(App\Provincia::class, function (Faker $faker) {

    return [
		'provincia' => $faker->state,
		'id_pais' => factory(\App\Pais::class)->create()->id
	];

});