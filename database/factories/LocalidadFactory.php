<?php

use Faker\Generator as Faker;
use Carbon\Carbon;

$factory->define(App\Localidad::class, function (Faker $faker) {

    return [
        'localidad' => $faker->city,
        'id_provincia' => factory(\App\Provincia::class)->create()->id
    ];

});