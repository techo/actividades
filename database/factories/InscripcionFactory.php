<?php

use Faker\Generator as Faker;
use Carbon\Carbon;

$factory->define(App\Inscripcion::class, function (Faker $faker) {

	$fecha = Carbon::parse(now());
	$fecha_fin = $fecha->copy()->addMinute();

    return [
		'idActividad' => function () {
            return factory(App\Actividad::class)->create()->idActividad;
        },
		'idPersona' => function () {
            return factory(App\Persona::class)->create()->idPersona;
        },
		'idUnidadOrganizacional' => null,
		'idEscuelaRol' => null,
		'idCuadrillaRol' => null,
		'idActividadRol' => null,
		'idMesaTrabajoRol' => null,
		'idProgramaRol' => null,
		'idMesaTrabajoLTRol' => null,
		'idLocalidadRol' => null,
		'rol' => $faker->jobTitle,
		'fechaInscripcion' => $fecha->format('Y-m-d H:i:s'),
		'fechaFin' => null,
		'estado' => 'Sin Contactar',
		'evaluacion' => 0,
		'aceptarCompromiso' =>  0,
		'acompanante' => '',
		'comentarios' => null,
		'fechaCreacion'  => $fecha->format('Y-m-d H:i:s'),
		'fechaModificacion'  => $fecha->format('Y-m-d H:i:s'),
		'idPersonaModificacion' => function () {
            return factory(App\Persona::class)->create()->idPersona;
        },
		'montoPago' => null,
		'moneda' => null,
		'subsidio' => null,
		'idRazonSubsidio' => null,
		'presente' => 0,
		'puntoEnvio' => null,
		'captacion' => null,
		'pago' => 0,
		'fechaPago' => null,
		'fechaSubsidio' => null,
		'idPuntoEncuentro' => function () {
            return factory(App\PuntoEncuentro::class)->create()->idPuntoEncuentro;
        },
		'idAreadeInteres' => null
    ];
});