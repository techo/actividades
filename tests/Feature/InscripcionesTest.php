<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;

class InscripcionesTest extends TestCase
{
	use RefreshDatabase;

    /** @test */
    public function usuario_se_puede_inscribir()
    {
        $this->withoutExceptionHandling();

        $persona = factory('App\Persona')->create();
        $persona->givePermissionTo(Permission::create(['name' => 'ver_backoffice']));

        $actividad = factory('App\Actividad')->create();
        $p = $actividad->puntosEncuentro()->save(factory('App\PuntoEncuentro')->make());

        $datos = [
            'punto_encuentro' => $p->idPuntoEncuentro, 
            'aceptar_terminos' => 1 
        ];
        
        $this->actingAs($persona)
            ->post('/inscripciones/actividad/' . $actividad->idActividad . '/gracias',$datos)
            ->assertStatus(200);

        $this->assertDatabaseHas('Inscripcion', [
        'idPuntoEncuentro' => $p->idPuntoEncuentro,
        'idActividad' => $actividad->idActividad,
        'idPersona' => $persona->idPersona
        ]);	

        $this->assertDatabaseHas('Grupo_Persona', [
        'idActividad' => $actividad->idActividad,
        'idPersona' => $persona->idPersona
        ]);
    }

    /** @test */
    public function usuario_se_puede_desinscribir()
    {
        $this->withoutExceptionHandling(); 

        $i = factory('App\Inscripcion')->create();

        $persona = $i->persona->givePermissionTo(Permission::create(['name' => 'ver_backoffice']));

        $this->actingAs($persona)
            ->delete('/ajax/usuario/inscripciones/' . $i->idInscripcion)
            ->assertStatus(200);

        $this->assertDatabaseHas('Inscripcion', [
        'idPuntoEncuentro' => $i->idPuntoEncuentro,
        'idActividad' => $i->idActividad,
        'idPersona' => $i->idPersona,
        'estado' => 'Desinscripto'
        ]);

        $this->assertDatabaseMissing('Grupo_Persona', [
        'idActividad' => $i->idActividad,
        'idPersona' => $i->idPersona
        ]);	
    }
}
