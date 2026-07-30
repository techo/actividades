<?php

namespace Tests\Feature\ajax;

use App\ActividadPregunta;
use App\Grupo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClonarActividadTest extends TestCase
{
    use RefreshDatabase;

    private function admin()
    {
        $this->seed('PermisosSeeder');
        $admin = factory('App\Persona')->create();
        $admin->assignRole('admin');

        return $admin;
    }

    private function clonar($admin, $actividad)
    {
        return $this->actingAs($admin)
            ->post('/admin/ajax/actividades/' . $actividad->idActividad . '/clonar', [
                'idActividad' => $actividad->idActividad,
            ]);
    }

    /** @test */
    public function clona_actividad_con_preguntas_condiciones_jornadas_puntos_y_grupos()
    {
        $this->withoutExceptionHandling();
        $admin = $this->admin();

        $actividad = factory('App\Actividad')->create();

        // Árbol de grupos: raíz + un subgrupo.
        $raiz = Grupo::create([
            'nombre'      => 'Raíz',
            'idPadre'     => 0,
            'idActividad' => $actividad->idActividad,
        ]);
        Grupo::create([
            'nombre'      => 'Subgrupo',
            'idPadre'     => $raiz->idGrupo,
            'idActividad' => $actividad->idActividad,
        ]);

        // Punto de encuentro + coordinador.
        factory('App\PuntoEncuentro')->create(['idActividad' => $actividad->idActividad]);
        factory('App\Coordinador')->create(['idActividad' => $actividad->idActividad]);

        // Jornada.
        $responsable = factory('App\Persona')->create();
        \App\Jornada::create([
            'nombre'      => 'Jornada 1',
            'idActividad' => $actividad->idActividad,
            'idPersona'   => $responsable->idPersona,
            'activo'      => 1,
        ]);

        // Preguntas: una padre desplegable y una dependiente que se muestra solo
        // si el padre respondió la opción "opt_n".
        $padre = $actividad->preguntas()->create([
            'pregunta'  => '¿Qué zona?',
            'tipo'      => 'desplegable',
            'opciones'  => [
                ['id' => 'opt_n', 'label' => 'Zona Norte'],
                ['id' => 'opt_s', 'label' => 'Zona Sur'],
            ],
            'requerida' => true,
            'orden'     => 1,
        ]);
        $dependiente = $actividad->preguntas()->create([
            'pregunta'  => 'Detalle Norte',
            'tipo'      => 'abierta',
            'requerida' => false,
            'orden'     => 2,
        ]);
        $dependiente->condiciones()->create([
            'parent_id' => $padre->id,
            'operator'  => 'equals',
            'value'     => 'opt_n',
        ]);

        // Act.
        $response = $this->clonar($admin, $actividad)->assertSuccessful();

        $clonId = $response->json('idActividad');
        $this->assertNotNull($clonId);
        $this->assertNotEquals($actividad->idActividad, $clonId);

        // Actividad clonada con nombre prefijado y creador reasignado.
        $this->assertDatabaseHas('Actividad', [
            'idActividad'       => $clonId,
            'nombreActividad'   => 'Copia de ' . $actividad->nombreActividad,
            'idPersonaCreacion' => $admin->idPersona,
        ]);

        // Relaciones simples copiadas.
        $this->assertDatabaseHas('PuntoEncuentro', ['idActividad' => $clonId]);
        $this->assertDatabaseHas('Coordinadores', ['idActividad' => $clonId]);
        $this->assertDatabaseHas('Jornada', ['idActividad' => $clonId, 'nombre' => 'Jornada 1']);

        // Árbol de grupos clonado (raíz + subgrupo).
        $this->assertDatabaseHas('Grupo', ['idActividad' => $clonId, 'idPadre' => 0]);
        $this->assertEquals(2, Grupo::where('idActividad', $clonId)->count());

        // Preguntas clonadas.
        $preguntasClon = ActividadPregunta::where('actividad_id', $clonId)->get();
        $this->assertCount(2, $preguntasClon);

        $padreClon       = $preguntasClon->firstWhere('pregunta', '¿Qué zona?');
        $dependienteClon = $preguntasClon->firstWhere('pregunta', 'Detalle Norte');
        $this->assertNotNull($padreClon);
        $this->assertNotNull($dependienteClon);

        // Los ids de las opciones (identidad estable) se preservan.
        $this->assertEquals(['Zona Norte', 'Zona Sur'], $padreClon->opciones);

        // La condición clonada apunta a las preguntas NUEVAS (remapeo de ids),
        // conservando el value (id estable de opción).
        $this->assertDatabaseHas('pregunta_condiciones', [
            'target_type' => 'actividad_pregunta',
            'target_id'   => $dependienteClon->id,
            'parent_id'   => $padreClon->id,
            'value'       => 'opt_n',
        ]);

        // En total: la condición original + la clonada, ninguna más.
        $this->assertEquals(2, \App\PreguntaCondicion::count());
    }

    /**
     * Regresión del bug reportado: las actividades legacy sin grupo raíz hacían
     * fallar el clonado con un TypeError no capturado (500). Ahora debe clonar
     * bien y crear un grupo raíz para el clon.
     *
     * @test
     */
    public function clona_actividad_legacy_sin_grupo_raiz_sin_error()
    {
        $this->withoutExceptionHandling();
        $admin = $this->admin();

        $actividad = factory('App\Actividad')->create();
        $this->assertDatabaseMissing('Grupo', ['idActividad' => $actividad->idActividad]);

        $response = $this->clonar($admin, $actividad)->assertSuccessful();

        $clonId = $response->json('idActividad');
        $this->assertDatabaseHas('Grupo', ['idActividad' => $clonId, 'idPadre' => 0]);
    }

    /** @test */
    public function clonar_actividad_inexistente_devuelve_404()
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post('/admin/ajax/actividades/999999/clonar', ['idActividad' => 999999])
            ->assertStatus(404);
    }
}
