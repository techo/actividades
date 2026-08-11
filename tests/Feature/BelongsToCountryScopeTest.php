<?php

namespace Tests\Feature;

use App\Actividad;
use App\Scopes\BelongsToCountryScope;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Task 40 — Global Scope BelongsToCountry (piloto: Actividad).
 *
 * Verifica el aislamiento por país y —crítico— que el scope sea inerte sin usuario
 * autenticado (comandos Artisan/jobs/colas), sin repetir el bug de Actividad::boot().
 */
class BelongsToCountryScopeTest extends TestCase
{
    use RefreshDatabase;

    private function actividadEnPais($paisId)
    {
        return factory('App\Actividad')->create(['idPais' => $paisId]);
    }

    private function inscripcionEnPais($paisId)
    {
        $act = $this->actividadEnPais($paisId);
        return factory('App\Inscripcion')->create(['idActividad' => $act->idActividad]);
    }

    /** @test */
    public function usuario_de_un_pais_solo_ve_actividades_de_su_pais()
    {
        $paisA = factory('App\Pais')->create();
        $paisB = factory('App\Pais')->create();
        $actA = $this->actividadEnPais($paisA->id);
        $actB = $this->actividadEnPais($paisB->id);

        $user = factory('App\Persona')->create(['idPaisPermitido' => $paisA->id]);
        $this->actingAs($user);

        $ids = Actividad::pluck('idActividad');
        $this->assertTrue($ids->contains($actA->idActividad), 'Debe ver la de su país');
        $this->assertFalse($ids->contains($actB->idActividad), 'No debe ver la de otro país');
    }

    /** @test */
    public function admin_global_ve_todas_las_actividades()
    {
        $paisA = factory('App\Pais')->create();
        $paisB = factory('App\Pais')->create();
        $actA = $this->actividadEnPais($paisA->id);
        $actB = $this->actividadEnPais($paisB->id);

        // idPaisPermitido 0 = admin global.
        $user = factory('App\Persona')->create(['idPaisPermitido' => 0]);
        $this->actingAs($user);

        $ids = Actividad::pluck('idActividad');
        $this->assertTrue($ids->contains($actA->idActividad));
        $this->assertTrue($ids->contains($actB->idActividad));
    }

    /** @test */
    public function sin_usuario_autenticado_el_scope_no_filtra()
    {
        // CLI/jobs/colas/seeders: no hay auth()->user(). El scope no debe filtrar ni romper.
        $paisA = factory('App\Pais')->create();
        $paisB = factory('App\Pais')->create();
        $this->actividadEnPais($paisA->id);
        $this->actividadEnPais($paisB->id);

        // Sin actingAs → guest.
        $this->assertSame(2, Actividad::count());
    }

    /** @test */
    public function todos_los_paises_ignora_el_scope()
    {
        $paisA = factory('App\Pais')->create();
        $paisB = factory('App\Pais')->create();
        $this->actividadEnPais($paisA->id);
        $this->actividadEnPais($paisB->id);

        $user = factory('App\Persona')->create(['idPaisPermitido' => $paisA->id]);
        $this->actingAs($user);

        $this->assertSame(1, Actividad::count(), 'scope activo: solo su país');
        $this->assertSame(2, Actividad::todosLosPaises()->count(), 'escape hatch: todos');
        // El scope se puede quitar también con la API estándar de Eloquent.
        $this->assertSame(2, Actividad::withoutGlobalScope(BelongsToCountryScope::class)->count());
    }

    /** @test */
    public function actualizar_actividad_sin_usuario_autenticado_no_rompe()
    {
        // Regresión del bug de Actividad::boot(): un update en contexto sin auth
        // (comando/seeder) accedía a auth()->user()->idPersona y reventaba.
        $act = factory('App\Actividad')->create();

        // Sin actingAs → guest.
        $act->nombreActividad = 'actualizado sin auth';
        $act->save();

        $this->assertDatabaseHas('Actividad', [
            'idActividad'     => $act->idActividad,
            'nombreActividad' => 'actualizado sin auth',
        ]);
    }

    // --- Inscripcion: país derivado de su actividad (whereHas) ---

    /** @test */
    public function inscripcion_se_aisla_por_pais_de_su_actividad()
    {
        $paisA = factory('App\Pais')->create();
        $paisB = factory('App\Pais')->create();
        $insA = $this->inscripcionEnPais($paisA->id);
        $insB = $this->inscripcionEnPais($paisB->id);

        $user = factory('App\Persona')->create(['idPaisPermitido' => $paisA->id]);
        $this->actingAs($user);

        $ids = \App\Inscripcion::pluck('idInscripcion');
        $this->assertTrue($ids->contains($insA->idInscripcion), 'Ve la de actividad de su país');
        $this->assertFalse($ids->contains($insB->idInscripcion), 'No ve la de otro país');
    }

    /** @test */
    public function inscripcion_sin_auth_no_filtra()
    {
        $this->inscripcionEnPais(factory('App\Pais')->create()->id);
        $this->inscripcionEnPais(factory('App\Pais')->create()->id);

        // Guest (CLI/jobs): no filtra.
        $this->assertSame(2, \App\Inscripcion::count());
    }
}
