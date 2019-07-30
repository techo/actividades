<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GruposTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function borrar_grupos_reasigna_al_grupo_raiz()
    {
        $this->withoutExceptionHandling();

        // crear actividad
        $actividad = factory('App\Actividad')->create();
        
        // crear grupo raiz de la actividad
        $grupo_raiz = factory('App\Grupo')->create([
            'idActividad' => $actividad->idActividad,
            'idPadre' => 0
        ]);


        // crear grupo nuevo a la actividad
        $grupo_nuevo = factory('App\Grupo')->create([
            'idActividad' => $actividad->idActividad,
            'idPadre' => $grupo_raiz->idGrupo
        ]);

        $otro_grupo_nuevo = factory('App\Grupo')->create([
            'idActividad' => $actividad->idActividad,
            'idPadre' => $grupo_raiz->idGrupo
        ]);

        // crear persona
        $persona = factory('App\Persona')->create();

        // crear persona
        $otra_persona = factory('App\Persona')->create();

        // agregar al grupo nuevo a esta persona
        $grupo_persona = factory('App\GrupoRolPersona')->create([
            'idActividad' => $actividad->idActividad,
            'idPersona' => $persona->idPersona,
            'idGrupo' => $grupo_nuevo->idGrupo
        ]);

        // agregar al grupo nuevo a esta persona
        $grupo_persona = factory('App\GrupoRolPersona')->create([
            'idActividad' => $actividad->idActividad,
            'idPersona' => $otra_persona->idPersona,
            'idGrupo' => $otro_grupo_nuevo->idGrupo
        ]);

        $this->seed('PermisosSeeder');

        $admin = factory('App\Persona')->create();
        $admin->assignRole('admin');


        // borrar grupo nuevo
        $response = $this->actingAs($admin)
            ->post('/admin/ajax/actividades/'. $actividad->idActividad 
                .'/grupos/borrar', [
                    'miembros' => [
                        ['id' => $grupo_nuevo->idGrupo, 'tipo' => 'grupo'],
                        ['id' => $otro_grupo_nuevo->idGrupo, 'tipo' => 'grupo'],
                    ]
                ])
            ->assertStatus(200);


        // verificar que persona este en grupo raiz de la actividad
        $this->assertDatabaseHas('Grupo_Persona', [
            'idGrupo' => $grupo_raiz->idGrupo,
            'idActividad' => $actividad->idActividad,
            'idPersona' => $persona->idPersona
        ]);

        $this->assertDatabaseHas('Grupo_Persona', [
            'idGrupo' => $grupo_raiz->idGrupo,
            'idActividad' => $actividad->idActividad,
            'idPersona' => $otra_persona->idPersona
        ]);

        $this->assertDatabaseMissing('Grupo', [
            'idGrupo' => $grupo_nuevo->idGrupo
        ]);

        $this->assertDatabaseMissing('Grupo', [
            'idGrupo' => $otro_grupo_nuevo->idGrupo
        ]);

        // verificar que persona NO este en grupo nuevo de la actividad
        $this->assertDatabaseMissing('Grupo_Persona', [
            'idGrupo' => $grupo_nuevo->idGrupo,
            'idActividad' => $actividad->idActividad,
            'idPersona' => $persona->idPersona
        ]);

        $this->assertDatabaseMissing('Grupo_Persona', [
            'idGrupo' => $otro_grupo_nuevo->idGrupo,
            'idActividad' => $actividad->idActividad,
            'idPersona' => $otra_persona->idPersona
        ]);


        

    }
}
