<?php

use Faker\Generator as Faker;

/*
 * Integrante mínimo válido: NOT NULL = idEquipo, idPersona, rol, estado,
 * fechaInicio (el resto de las columnas son nullable). Encadena Equipo y
 * Persona; en los tests conviene pasar idEquipo para agrupar integrantes del
 * mismo equipo: factory('App\Integrante')->create(['idEquipo' => $equipo->idEquipo]).
 */
$factory->define(App\Integrante::class, function (Faker $faker) {
    return [
        'idEquipo' => factory('App\Equipo')->create()->idEquipo,
        'idPersona' => factory('App\Persona')->create()->idPersona,
        'rol' => $faker->word,
        'estado' => 1,
        'fechaInicio' => \Carbon\Carbon::now(),
    ];
});
