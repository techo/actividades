<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;

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

    /** @test */
    public function solo_persona_presente_puede_evaluar()
    {
      
        $actividad = app(ActividadFactory::class)
            ->agregarPuntoConInscriptos(0)
            ->conGrupoRaiz()
            ->conEstado('evaluable')
            ->create();

        $maria = factory('App\Persona')->create();

        $datos = [
            'idPersona' => $maria->idPersona,
            'idPuntoEncuentro' => $actividad->puntosEncuentro->first()->idPuntoEncuentro,
            'notificar' => 0,
        ];

        $inscripcion = factory('App\Inscripcion')->create([
            'idActividad' => $actividad->idActividad,
            'idPersona' => $maria->idPersona,
            'idPuntoEncuentro' => $actividad->puntosEncuentro->first()->idPuntoEncuentro,
        ]);

        $maria = $maria->givePermissionTo(Permission::create(['name' => 'ver_backoffice']));
        // puntuar actividad incripto pero sin presente (esperar error)
        $this->actingAs($maria)
            ->get('/actividades/' . $actividad->idActividad . '/evaluaciones')
            ->assertStatus(403);

        $inscripcion = factory('App\Inscripcion')->create([
            'idActividad' => $actividad->idActividad,
            'idPersona' => $maria->idPersona,
            'idPuntoEncuentro' => $actividad->puntosEncuentro->first()->idPuntoEncuentro,
            'presente' => true,
        ]);

        $this->actingAs($maria)
            ->get('/actividades/' . $actividad->idActividad . '/evaluaciones')
            ->assertStatus(200);

        // dar presente a usuario

        // puntuar actividad (esperar ok)
        // $this->actingAs($maria)
        //     ->get('/actividades/'.$actividad->idActividad.'/evaluaciones')
        //     ->assertStatus(200);
    }   

}
