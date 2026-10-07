<?php

namespace Tests\Feature;

use App\ActividadFactory;
use App\Grupo;
use App\GrupoRolPersona;
use App\Inscripcion;
use App\Persona;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Task 47 — integridad de grupos (reclamos #7/#11 de producción).
 */
class GruposIntegridadTest extends TestCase
{
    use RefreshDatabase;

    private $admin;
    private $pais;

    protected function setUp()
    {
        parent::setUp();
        $this->seed('PermisosSeeder');
        $this->admin = factory(Persona::class)->create();
        $this->admin->assignRole('admin');
        $this->pais = factory('App\Pais')->create();
    }

    private function actividadConRaiz($nombre = 'Construcción')
    {
        $actividad = app(ActividadFactory::class)->creadaPor($this->admin)->conPais($this->pais)
            ->create(['nombreActividad' => $nombre]);
        $raiz = factory(Grupo::class)->create([
            'idActividad' => $actividad->idActividad, 'idPadre' => 0, 'nombre' => $nombre,
        ]);
        return [$actividad, $raiz];
    }

    private function inscribir($actividad, $persona, $idGrupo = null)
    {
        factory(Inscripcion::class)->create([
            'idActividad' => $actividad->idActividad, 'idPersona' => $persona->idPersona,
        ]);
        if ($idGrupo) {
            GrupoRolPersona::create([
                'idActividad' => $actividad->idActividad, 'idPersona' => $persona->idPersona,
                'idGrupo' => $idGrupo, 'rol' => '',
            ]);
        }
    }

    /** @test */
    public function quitar_persona_de_un_grupo_no_toca_su_membresia_en_otras_actividades()
    {
        [$a, $raizA] = $this->actividadConRaiz('A');
        [$b, $raizB] = $this->actividadConRaiz('B');
        $subA = factory(Grupo::class)->create(['idActividad' => $a->idActividad, 'idPadre' => $raizA->idGrupo]);
        $subB = factory(Grupo::class)->create(['idActividad' => $b->idActividad, 'idPadre' => $raizB->idGrupo]);
        $persona = factory(Persona::class)->create();
        $this->inscribir($a, $persona, $subA->idGrupo);
        $this->inscribir($b, $persona, $subB->idGrupo);

        $this->actingAs($this->admin)
            ->post("/admin/ajax/actividades/{$a->idActividad}/grupos/borrar", [
                'miembros' => [['id' => $persona->idPersona, 'tipo' => 'persona']],
            ])->assertStatus(200);

        // En A vuelve a la raíz (no se borra), en B queda intacta.
        $this->assertDatabaseHas('Grupo_Persona', ['idActividad' => $a->idActividad, 'idPersona' => $persona->idPersona, 'idGrupo' => $raizA->idGrupo]);
        $this->assertDatabaseHas('Grupo_Persona', ['idActividad' => $b->idActividad, 'idPersona' => $persona->idPersona, 'idGrupo' => $subB->idGrupo]);
    }

    /** @test */
    public function borrar_un_grupo_borra_todos_sus_descendientes_y_reasigna_a_la_raiz()
    {
        [$a, $raiz] = $this->actividadConRaiz();
        $escuela = factory(Grupo::class)->create(['idActividad' => $a->idActividad, 'idPadre' => $raiz->idGrupo]);
        $cuadrilla = factory(Grupo::class)->create(['idActividad' => $a->idActividad, 'idPadre' => $escuela->idGrupo]);
        $sub = factory(Grupo::class)->create(['idActividad' => $a->idActividad, 'idPadre' => $cuadrilla->idGrupo]);
        $p1 = factory(Persona::class)->create();
        $p2 = factory(Persona::class)->create();
        $this->inscribir($a, $p1, $cuadrilla->idGrupo); // grupo intermedio: antes quedaba huérfano
        $this->inscribir($a, $p2, $sub->idGrupo);

        $this->actingAs($this->admin)
            ->post("/admin/ajax/actividades/{$a->idActividad}/grupos/borrar", [
                'miembros' => [['id' => $escuela->idGrupo, 'tipo' => 'grupo']],
            ])->assertStatus(200);

        foreach ([$escuela, $cuadrilla, $sub] as $g) {
            $this->assertDatabaseMissing('Grupo', ['idGrupo' => $g->idGrupo]);
        }
        $this->assertDatabaseHas('Grupo', ['idGrupo' => $raiz->idGrupo]);
        $this->assertDatabaseHas('Grupo_Persona', ['idPersona' => $p1->idPersona, 'idGrupo' => $raiz->idGrupo]);
        $this->assertDatabaseHas('Grupo_Persona', ['idPersona' => $p2->idPersona, 'idGrupo' => $raiz->idGrupo]);
    }

    /** @test */
    public function no_se_pueden_borrar_grupos_de_otra_actividad()
    {
        [$a] = $this->actividadConRaiz('A');
        [$b, $raizB] = $this->actividadConRaiz('B');
        $grupoB = factory(Grupo::class)->create(['idActividad' => $b->idActividad, 'idPadre' => $raizB->idGrupo]);

        $this->actingAs($this->admin)
            ->post("/admin/ajax/actividades/{$a->idActividad}/grupos/borrar", [
                'miembros' => [['id' => $grupoB->idGrupo, 'tipo' => 'grupo']],
            ])->assertStatus(200);

        $this->assertDatabaseHas('Grupo', ['idGrupo' => $grupoB->idGrupo]);
    }

    /** @test */
    public function incluir_inscripto_sin_membresia_la_crea_en_el_grupo_destino()
    {
        [$a, $raiz] = $this->actividadConRaiz();
        $grupo = factory(Grupo::class)->create(['idActividad' => $a->idActividad, 'idPadre' => $raiz->idGrupo]);
        $persona = factory(Persona::class)->create();
        $this->inscribir($a, $persona); // sin Grupo_Persona (antes: 500 "Undefined variable: grupo")

        $this->actingAs($this->admin)
            ->post("/admin/ajax/grupos/{$grupo->idGrupo}/inscriptos", [
                'idPersona' => $persona->idPersona, 'idGrupo' => $grupo->idGrupo, 'idActividad' => $a->idActividad,
            ])->assertStatus(200);

        $this->assertDatabaseHas('Grupo_Persona', ['idActividad' => $a->idActividad, 'idPersona' => $persona->idPersona, 'idGrupo' => $grupo->idGrupo]);
    }

    /** @test */
    public function incluir_inscripto_en_raiz_lo_mueve_y_en_subgrupo_devuelve_428()
    {
        [$a, $raiz] = $this->actividadConRaiz();
        $g1 = factory(Grupo::class)->create(['idActividad' => $a->idActividad, 'idPadre' => $raiz->idGrupo]);
        $g2 = factory(Grupo::class)->create(['idActividad' => $a->idActividad, 'idPadre' => $raiz->idGrupo]);
        $enRaiz = factory(Persona::class)->create();
        $enG1 = factory(Persona::class)->create();
        $this->inscribir($a, $enRaiz, $raiz->idGrupo);
        $this->inscribir($a, $enG1, $g1->idGrupo);

        $this->actingAs($this->admin)
            ->post("/admin/ajax/grupos/{$g2->idGrupo}/inscriptos", ['idPersona' => $enRaiz->idPersona, 'idGrupo' => $g2->idGrupo, 'idActividad' => $a->idActividad])
            ->assertStatus(200);
        $this->assertDatabaseHas('Grupo_Persona', ['idPersona' => $enRaiz->idPersona, 'idGrupo' => $g2->idGrupo]);

        $this->actingAs($this->admin)
            ->post("/admin/ajax/grupos/{$g2->idGrupo}/inscriptos", ['idPersona' => $enG1->idPersona, 'idGrupo' => $g2->idGrupo, 'idActividad' => $a->idActividad])
            ->assertStatus(428);
    }

    /** @test */
    public function incluir_no_inscripto_devuelve_422_con_mensaje()
    {
        [$a, $raiz] = $this->actividadConRaiz();
        $grupo = factory(Grupo::class)->create(['idActividad' => $a->idActividad, 'idPadre' => $raiz->idGrupo]);
        $persona = factory(Persona::class)->create();

        $this->actingAs($this->admin)
            ->postJson("/admin/ajax/grupos/{$grupo->idGrupo}/inscriptos", ['idPersona' => $persona->idPersona, 'idGrupo' => $grupo->idGrupo, 'idActividad' => $a->idActividad])
            ->assertStatus(422)
            ->assertJsonStructure(['message']);

        $this->assertDatabaseMissing('Grupo_Persona', ['idPersona' => $persona->idPersona]);
    }

    /** @test */
    public function incluir_en_grupo_de_otra_actividad_da_404()
    {
        [$a] = $this->actividadConRaiz('A');
        [$b, $raizB] = $this->actividadConRaiz('B');
        $persona = factory(Persona::class)->create();
        $this->inscribir($a, $persona);

        $this->actingAs($this->admin)
            ->post("/admin/ajax/grupos/{$raizB->idGrupo}/inscriptos", ['idPersona' => $persona->idPersona, 'idGrupo' => $raizB->idGrupo, 'idActividad' => $a->idActividad])
            ->assertStatus(404);
    }

    /** @test */
    public function la_raiz_se_resuelve_por_idPadre_y_no_por_nombre()
    {
        [$a, $raiz] = $this->actividadConRaiz('Nombre viejo');
        $a->nombreActividad = 'Nombre nuevo (renombrada o clonada)';
        $a->save();

        $this->assertEquals($raiz->idGrupo, $a->fresh()->obtenerGrupoRaiz()->idGrupo);
        $this->assertEquals(1, Grupo::where('idActividad', $a->idActividad)->where('idPadre', 0)->count());
    }

    /** @test */
    public function asignar_grupo_masivo_crea_la_membresia_faltante_y_valida_el_grupo()
    {
        [$a, $raiz] = $this->actividadConRaiz();
        $grupo = factory(Grupo::class)->create(['idActividad' => $a->idActividad, 'idPadre' => $raiz->idGrupo]);
        $persona = factory(Persona::class)->create();
        $this->inscribir($a, $persona);
        $insc = Inscripcion::where('idPersona', $persona->idPersona)->first();
        $url = "/admin/ajax/actividades/{$a->idActividad}/inscripciones/asignar/grupo";

        $this->actingAs($this->admin)
            ->post($url, ['grupo' => ['idGrupo' => $grupo->idGrupo, 'nombre' => $grupo->nombre], 'inscripciones' => [$insc->idInscripcion]])
            ->assertStatus(200);
        $this->assertDatabaseHas('Grupo_Persona', ['idPersona' => $persona->idPersona, 'idGrupo' => $grupo->idGrupo]);
        $this->assertEquals(1, GrupoRolPersona::where('idPersona', $persona->idPersona)->count());

        // Sin grupo elegido: antes 500 "Undefined index: grupo"; ahora validación.
        $this->actingAs($this->admin)
            ->postJson($url, ['inscripciones' => [$insc->idInscripcion]])
            ->assertStatus(422);
    }

    /** @test */
    public function comando_repara_inscripciones_sin_grupo_solo_con_commit()
    {
        [$a, $raiz] = $this->actividadConRaiz();
        $a->fechaInicio = now()->addDays(3);
        $a->save();
        $persona = factory(Persona::class)->create();
        $this->inscribir($a, $persona);

        $this->artisan('grupos:reparar-membresias', ['--actividad' => $a->idActividad])->assertExitCode(0);
        $this->assertDatabaseMissing('Grupo_Persona', ['idPersona' => $persona->idPersona]);

        $this->artisan('grupos:reparar-membresias', ['--actividad' => $a->idActividad, '--commit' => true])->assertExitCode(0);
        $this->assertDatabaseHas('Grupo_Persona', ['idPersona' => $persona->idPersona, 'idGrupo' => $raiz->idGrupo]);
    }
}
