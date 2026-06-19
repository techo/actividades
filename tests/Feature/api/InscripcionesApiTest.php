<?php

namespace Tests\Feature\api;

use App\ActividadFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Laravel\Passport\Passport;
use Tests\TestCase;

/**
 * Tarea #16 — Tests de la API mobile: inscripciones desde la app.
 * El flujo de inscripción mobile usa endpoints propios (InscripcionesController@create
 * con respuesta JSON) distintos del flujo web.
 */
class InscripcionesApiTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function usuario_se_inscribe_a_una_actividad_simple()
    {
        $this->seed('PermisosSeeder');
        Mail::fake();

        $persona = factory('App\Persona')->create();
        Passport::actingAs($persona);

        $actividad = app(ActividadFactory::class)
            ->conGrupoRaiz()
            ->agregarPuntoConInscriptos(0)
            ->create();
        $punto = $actividad->puntosEncuentro[0];

        $this->postJson('/api/inscripciones/actividad/' . $actividad->idActividad, [
            'punto_encuentro'  => $punto->idPuntoEncuentro,
            'aceptar_terminos' => 1,
        ])
            ->assertStatus(200)
            ->assertJson([ 'success' => true, 'estado_inscripcion' => 'CONFIRMADO' ]);

        $this->assertDatabaseHas('Inscripcion', [
            'idActividad' => $actividad->idActividad,
            'idPersona'   => $persona->idPersona,
        ]);
    }

    /** @test */
    public function mis_inscripciones_lista_las_del_usuario_autenticado()
    {
        $this->seed('PermisosSeeder');

        $persona = factory('App\Persona')->create();
        Passport::actingAs($persona);

        $actividad = app(ActividadFactory::class)->agregarPuntoConInscriptos(0)->create();
        factory('App\Inscripcion')->create([
            'idActividad'      => $actividad->idActividad,
            'idPuntoEncuentro' => $actividad->puntosEncuentro[0]->idPuntoEncuentro,
            'idPersona'        => $persona->idPersona,
        ]);

        // Otra persona inscripta a otra actividad: no debe aparecer.
        $otra = app(ActividadFactory::class)->agregarPuntoConInscriptos(1)->create();

        $this->getJson('/api/inscripciones')
            ->assertStatus(200)
            ->assertJsonCount(1);
    }

    /** @test */
    public function usuario_puede_cancelar_su_inscripcion()
    {
        $this->seed('PermisosSeeder');

        $persona = factory('App\Persona')->create();
        Passport::actingAs($persona);

        $actividad = app(ActividadFactory::class)->agregarPuntoConInscriptos(0)->create();
        $inscripcion = factory('App\Inscripcion')->create([
            'idActividad'      => $actividad->idActividad,
            'idPuntoEncuentro' => $actividad->puntosEncuentro[0]->idPuntoEncuentro,
            'idPersona'        => $persona->idPersona,
        ]);

        $this->deleteJson('/api/inscripciones/' . $actividad->idActividad)
            ->assertStatus(200)
            ->assertJson([ 'success' => true ]);

        $this->assertSoftDeleted('Inscripcion', [ 'idInscripcion' => $inscripcion->idInscripcion ]);
    }

    /** @test */
    public function inscribirse_a_un_punto_cerrado_devuelve_error()
    {
        $this->seed('PermisosSeeder');
        Mail::fake();

        $persona = factory('App\Persona')->create();
        Passport::actingAs($persona);

        $actividad = app(ActividadFactory::class)
            ->conGrupoRaiz()
            ->agregarPuntoConInscriptos(0)
            ->create();
        $punto = $actividad->puntosEncuentro[0];
        $punto->estado = 0;
        $punto->save();

        $this->postJson('/api/inscripciones/actividad/' . $actividad->idActividad, [
            'punto_encuentro'  => $punto->idPuntoEncuentro,
            'aceptar_terminos' => 1,
        ])
            ->assertStatus(200)
            ->assertJson([ 'success' => false, 'estado_inscripcion' => 'PUNTO_ENCUENTRO_CERRADO' ]);

        $this->assertDatabaseMissing('Inscripcion', [
            'idActividad' => $actividad->idActividad,
            'idPersona'   => $persona->idPersona,
        ]);
    }
}
