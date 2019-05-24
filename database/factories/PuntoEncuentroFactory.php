<?php

use Faker\Generator as Faker;
use Carbon\Carbon;

$factory->define(App\PuntoEncuentro::class, function (Faker $faker) {

    return [
      'punto'=> $faker->name,
      'horario' => '00:00:00',
      'idPersona' => factory(App\Persona::class)->create(),
      'idPais' => factory(App\Pais::class)->create(),
      'idProvincia' => factory(App\Provincia::class)->create(),
      'idLocalidad' => factory(App\Localidad::class)->create()
    ];
});