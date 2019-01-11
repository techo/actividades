<?php

use Faker\Generator as Faker;
use Carbon\Carbon;

$factory->define(App\GrupoRolPersona::class, function (Faker $faker) {

    return [
		'idPersona' => function () {
            return factory(App\Persona::class)->create()->idPersona;
        },
		'idGrupo' => function () {
            return factory(App\Grupo::class)->create()->idGrupo;
        },
		'idActividad' => function () {
            return factory(App\Actividad::class)->create()->idActividad;
        },
		'rol' => $faker->jobTitle
    ];

});