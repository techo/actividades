<?php

use Faker\Generator as Faker;

$factory->define(App\Localidad::class, function (Faker $faker) {

    return [
        'localidad' => $faker->name,
        'id_provincia' => factory(\App\Provincia::class)->create()->id
    ];

});
