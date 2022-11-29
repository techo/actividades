<?php

use Faker\Generator as Faker;

$factory->define(App\Persona::class, function (Faker $faker) {
    return [
    'dni'   => rand(1000000, 99999999),
    'mail'  => $faker->email,
    'genero'  => array_rand(['M', 'F']),
    'idPais'    => factory('App\Pais')->create(),
    'nombres'   => $faker->firstName,
    'password'  => Hash::make('password'),
    'idLocalidad'   => 289,
    'idProvincia'   => 3,
    'telefonoMovil' => $faker->phoneNumber,
    'apellidoPaterno'   => $faker->lastName,
    'fechaNacimiento'   => $faker->date('Y-m-d', '2000-2-31'),
    'idUnidadOrganizacional' => 0,
    'google_id' => '',
    'facebook_id' => '',
    'recibirMails' => 0,
    'email_verified_at' => $faker->date('Y-m-d', '2000-2-31'),
    ];
});
