<?php

use Faker\Generator as Faker;

$factory->define(App\GrupoRolPersona::class, function (Faker $faker) {
    return [
			'idActividad' => factory('App\Actividad')->create(),
			'idGrupo' => factory('App\Grupo')->create(),
			'idPersona' => factory('App\Persona')->create(),
			'rol' => $faker->word,
    ];
});
