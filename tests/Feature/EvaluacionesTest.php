<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use \App\ActividadFactory;

class EvaluacionesTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function solo_administrador_puede_ver_evaluaciones_de_usuario()
    {
        //$this->withoutExceptionHandling(); 

        $this->seed('PermisosSeeder');

        $admin = factory('App\Persona')->create();
        $admin->assignRole('admin');

        $coordinador = factory('App\Persona')->create();
        $coordinador->assignRole('coordinador');

        $nestor = factory('App\Persona')->create();

        app(ActividadFactory::class)->agregarEvaluacionDePersona($nestor)->create();
        app(ActividadFactory::class)->agregarEvaluacionDePersona($nestor)->create();
        app(ActividadFactory::class)->agregarEvaluacionDePersona($nestor)->create();

        $response = $this->actingAs($admin)
            ->get('/admin/ajax/usuarios/'. $nestor->idPersona .'/evaluaciones')
            ->assertStatus(200);

        $this->assertTrue(count(json_decode($response->getContent())->data) == 3);

        $response = $this->actingAs($coordinador)
            ->get('/admin/ajax/usuarios/'. $nestor->idPersona .'/evaluaciones')
            ->assertStatus(403);
    }

}
