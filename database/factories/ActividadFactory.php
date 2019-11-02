<?php

use Faker\Generator as Faker;
use Carbon\Carbon;

$factory->define(App\Actividad::class, function (Faker $faker) {

      $fecha = Carbon::now()->addMinutes(10);

    return [
      'nombreActividad' => $faker->name,
      'descripcion' => $faker->name,
      'estadoConstruccion' => "Abierta",

      'idTipo' => factory(\App\Tipo::class)->create(),
      'idCoordinador' => factory(App\Persona::class)->create(),
      'idOficina' => 1,

      'fechaInicio' => $fecha->format('Y-m-d H:i:s'),
      'fechaFin' => function($actividad) { 
            return Carbon::parse($actividad['fechaInicio'])->addMinute()->format('Y-m-d H:i:s');
      },
      'fechaInicioInscripciones' => function($actividad) { 
            return Carbon::parse($actividad['fechaInicio'])->subDays(10)->format('Y-m-d H:i:s');
      },
      'fechaFinInscripciones' => function($actividad) { 
            return Carbon::parse($actividad['fechaInicio'])->subMinute()->format('Y-m-d H:i:s');
      },
      'fechaInicioEvaluaciones' => function($actividad) { 
            return Carbon::parse($actividad['fechaFin'])->addMinute()->format('Y-m-d H:i:s');
      },
      'fechaFinEvaluaciones' => function($actividad) { 
            return Carbon::parse($actividad['fechaFin'])->addDays(10)->format('Y-m-d H:i:s');
      },

      'confirmacion' => 0,
      'pago' => 0,

      'fechaCreacion' => $fecha->format('Y-m-d H:i:s'),
      'fechaModificacion' => $fecha->format('Y-m-d H:i:s'),
      'idPersonaCreacion' => 1,
      'idPersonaModificacion' => 1,

      'lugar' => "",
      'idPais' => factory(App\Pais::class)->create(),
      'idProvincia' => factory(App\Provincia::class)->create(),
      'idLocalidad' => factory(App\Localidad::class)->create(),

      'limiteInscripciones' => "0",      
      'mensajeInscripcion' => $faker->name,
      'inscripcionInterna' => 0,

      'costo' => null,
      'montoMin' => "100.00",
      'montoMax' => "0.00",
      'moneda' => "ARS",
      'beca' => null,
      'fechaLimitePago' => null,

      'idUnidadOrganizacional' => 1,
    ];
});

$factory->state(App\Actividad::class, 'futura', [
    'fechaInicio' => Carbon::now()->addDays(5)->format('Y-m-d H:i:s')
]);

$factory->state(App\Actividad::class, 'pasada', [
    'fechaInicio' => Carbon::now()->subDays(5)->format('Y-m-d H:i:s')
]);

$factory->state(App\Actividad::class, 'con confirmacion', [
    'confirmacion' => 1,
]);

$factory->state(App\Actividad::class, 'con pago', [
    'pago' => 1,
]);

$factory->state(App\Actividad::class, 'con confirmacion y pago', [
    'confirmacion' => 1,
    'pago' => 1,
]);