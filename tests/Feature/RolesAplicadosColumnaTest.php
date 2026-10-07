<?php

namespace Tests\Feature;

use App\ActividadFactory;
use App\Inscripcion;
use App\Persona;
use App\Services\Listados\InscripcionesCatalogo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Columna "Roles a los que aplicó" en el listado de inscripciones (reclamo #9) y cerco de
 * asignarRol a la actividad de la ruta.
 */
class RolesAplicadosColumnaTest extends TestCase
{
    use RefreshDatabase;

    private function keys(array $campos)
    {
        return collect($campos)->pluck('key')->filter()->values()->all();
    }

    /** @test */
    public function la_columna_aparece_y_es_default_solo_si_la_actividad_tiene_roles()
    {
        $conRoles = app(ActividadFactory::class)->create(['roles_tags' => ['monitor', 'intendencia']]);
        $sinRoles = app(ActividadFactory::class)->create(['roles_tags' => null]);
        $catalogo = new InscripcionesCatalogo();

        $defaults = $this->keys($catalogo->defaultFields($conRoles->idActividad));
        $this->assertContains('rolesAplicados', $defaults);
        // Justo después de "Rol" (el confirmado).
        $this->assertEquals(array_search('rolesActividad', $defaults) + 1, array_search('rolesAplicados', $defaults));

        $this->assertNotContains('rolesAplicados', $this->keys($catalogo->defaultFields($sinRoles->idActividad)));
    }

    /** @test */
    public function asignar_rol_solo_toca_inscripciones_de_la_actividad_de_la_ruta()
    {
        $this->seed('PermisosSeeder');
        $admin = factory(Persona::class)->create();
        $admin->assignRole('admin');
        $pais = factory('App\Pais')->create();
        $a = app(ActividadFactory::class)->creadaPor($admin)->conPais($pais)->create(['roles_tags' => ['monitor']]);
        $b = app(ActividadFactory::class)->creadaPor($admin)->conPais($pais)->create();
        $deA = factory(Inscripcion::class)->create(['idActividad' => $a->idActividad, 'roles_aplicados' => ['monitor']]);
        $deB = factory(Inscripcion::class)->create(['idActividad' => $b->idActividad, 'rol' => 'intendencia']);

        $this->actingAs($admin)
            ->postJson("/admin/ajax/actividades/{$a->idActividad}/inscripciones/asignar/rol", [
                'rol' => 'monitor', 'inscripciones' => [$deA->idInscripcion, $deB->idInscripcion],
            ])->assertStatus(200);

        $this->assertEquals('monitor', $deA->fresh()->rol);
        $this->assertEquals('intendencia', $deB->fresh()->rol);
    }
}
