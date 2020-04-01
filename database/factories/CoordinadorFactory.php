<?php

use Faker\Generator as Faker;
use Carbon\Carbon;

$factory->define(App\Coordinador::class, function (Faker $faker) {

    return [
      'idPersona' => factory(App\Persona::class)->create(),
    ];

});

