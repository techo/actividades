<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
//use Illuminate\Foundation\Testing\RefreshDatabase;
use App\PuntoEncuentro;
use App\Actividad;
use App\Persona;

class InscripcionesTest extends TestCase
{
	//use RefreshDatabase;

	protected $actividad;
	protected $punto_encuentro;

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testInscribirUsuario()
    {

        $actividad = factory(\App\Actividad::class)->create();
        $persona = factory(\App\Persona::class)->create();

        $p = new PuntoEncuentro();
        $p->punto = 'Punto de prueba';
        $p->horario = '00:00:00';
        $p->idActividad = $actividad->id;
        $p->idPersona = $persona->id;
        $p->idPais = 1;
        $p->idLocalidad = 1;
        $p->idProvincia = 1;
        $p->save();

        $punto_encuentro = $p;

        $url = '/inscripciones/actividad/' . $actividad->idActividad . '/gracias';
        $form_data = ['punto_encuentro' => $punto_encuentro->idPuntoEncuentro,
        'aceptar_terminos' => 1 ];
        
		$this->withoutMiddleware();
        $response = $this->actingAs($persona)->post($url,$form_data);
        $response->assertStatus(200);

        $this->assertDatabaseHas('Inscripcion', [
        'idPuntoEncuentro' => $punto_encuentro->idPuntoEncuentro,
        'idActividad' => $actividad->idActividad,
        'idPersona' => $persona->idPersona
        ]);	

        $this->assertDatabaseHas('Grupo_Persona', [
        'idActividad' => $actividad->idActividad,
        'idPersona' => $persona->idPersona
        ]);

        $this->assertTrue(true);
    }

    public function testDesinscribirUsuario()
    {

    	$actividad = factory(\App\Actividad::class)->create();
        $persona = factory(\App\Persona::class)->create();

        $p = new PuntoEncuentro();
        $p->punto = 'Punto de prueba';
        $p->horario = '00:00:00';
        $p->idActividad = $actividad->id;
        $p->idPersona = $persona->id;
        $p->idPais = 1;
        $p->idLocalidad = 1;
        $p->idProvincia = 1;
        $p->save();

        $punto_encuentro = $p;

        $url = '/inscripciones/actividad/' . $actividad->idActividad . '/gracias';
        $form_data = ['punto_encuentro' => $punto_encuentro->idPuntoEncuentro,
        'aceptar_terminos' => 1 ];
        
		$this->withoutMiddleware();
        $response = $this->actingAs($persona)->post($url,$form_data);

        $url = '/ajax/usuario/inscripciones/' . $actividad->idActividad;
        
		$this->withoutMiddleware();
        $response = $this->actingAs($persona)->delete($url);

        $this->assertDatabaseHas('Inscripcion', [
        'idPuntoEncuentro' => $punto_encuentro->idPuntoEncuentro,
        'idActividad' => $actividad->idActividad,
        'idPersona' => $persona->idPersona,
        'estado' => 'Desinscripto'
        ]);

        $this->assertDatabaseMissing('Grupo_Persona', [
        'idActividad' => $actividad->idActividad,
        'idPersona' => $persona->idPersona
        ]);	

        $this->assertTrue(true);
    }
}
