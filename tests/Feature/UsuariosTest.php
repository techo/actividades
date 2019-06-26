<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use \App\ActividadFactory;

class UsuariosTest extends TestCase
{
	use RefreshDatabase;

	/** @test */
	public function puede_eliminar_su_cuenta()
	{
		$this->withoutExceptionHandling();

		$maria = factory('App\Persona')->create();

        $actividad = app(ActividadFactory::class)
        	->conEstado('futura')
            ->agregarInscripto($maria)
            ->create();

        $otra_actividad = app(ActividadFactory::class)
        	->conEstado('pasada')
            ->agregarInscripto($maria)
            ->create();

        $response = $this->actingAs($maria)
            ->delete('/ajax/usuario')
            ->assertStatus(302);
            
        $this->assertDatabaseHas('Persona',[
        	'idPersona' => $maria->idPersona,
        	'nombres' => 'Usuario eliminado',
        ]);

        $this->assertTrue($maria->inscripciones()->count() == 1);
		
	}

    /** @test */
    public function puede_ver_solo_actividades_que_esta_inscripto()
    {
        //$this->withoutExceptionHandling();
        
        $maria = factory('App\Persona')->create();

        $actividad = app(ActividadFactory::class)
            ->agregarInscripto($maria)
            ->create();

        $actividad_de_donde_me_desinscribo = app(ActividadFactory::class)
            ->agregarInscripto($maria)
            ->create();

        $actividad_de_donde_me_desinscribo->inscripciones->first()->delete();

        $response = $this->actingAs($maria)
            ->get('/ajax/usuario/inscripciones?date=')
            ->assertStatus(200);
            
        $this->assertTrue(count(json_decode($response->getContent())->data) == 1);
    }
}
