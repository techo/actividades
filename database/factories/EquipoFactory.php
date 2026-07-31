<?php

use Faker\Generator as Faker;

/*
 * Equipo mínimo válido: satisface las NOT NULL (idOficina, idPais, area,
 * nombre, activo, fechaInicio). Encadena Oficina y Pais. En los tests se puede
 * sobreescribir cualquier atributo, ej. factory('App\Equipo')->create(['activo' => 0]).
 */
$factory->define(App\Equipo::class, function (Faker $faker) {
    return [
        'idOficina' => factory('App\Oficina')->create()->id,
        'idPais' => factory('App\Pais')->create()->id,
        'nombre' => $faker->company,
        'area' => $faker->word,
        'activo' => 1,
        'fechaInicio' => \Carbon\Carbon::now(),
    ];
});
