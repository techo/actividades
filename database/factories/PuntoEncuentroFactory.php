<?php

use Faker\Generator as Faker;
use Carbon\Carbon;

$factory->define(App\PuntoEncuentro::class, function (Faker $faker) {

    return [
      'punto'=> $faker->name,
      'horario' => '00:00:00',
      'idPersona' => factory(\App\Persona::class)->create()->idPersona,
      'idPais' => factory(\App\Pais::class)->create()->id,
      'idProvincia' => factory(\App\Provincia::class)->create()->id,
      'idLocalidad' => factory(\App\Localidad::class)->create()->id
    ];
});