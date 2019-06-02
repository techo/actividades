<?php

use Faker\Generator as Faker;

$factory->define(App\Persona::class, function (Faker $faker) {
    return [
    'dni'   => rand(1000000, 99999999),
    'mail'  => $faker->email,
    'sexo'  => array_rand(['M', 'F']),
    'idPais'    => 1,
    'nombres'   => $faker->firstName,
    'carrera'   => $faker->words(3, true),
    'idCiudad'  => 0,
    'password'  => Hash::make('password'),
    'lenguaje'  => '',
    'statusCTCT'    => '',
    'idRegionLT'    => 0,
    'anoEstudio'    => rand(1,9),
    'idLocalidad'   => 289,
    'idProvincia'   => 3,
    'telefonoMovil' => $faker->phoneNumber,
    'idContactoCTCT'    => '',
    'apellidoPaterno'   => $faker->lastName,
    'fechaNacimiento'   => $faker->date('Y-m-d', '2000-2-31'),
    'idPaisResidencia'  => 1,
    'idUnidadOrganizacional' => 0,
    'google_id' => '',
    'facebook_id' => ''
    ];
});
