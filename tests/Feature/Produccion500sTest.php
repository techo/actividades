<?php

namespace Tests\Feature;

use App\CoordinadorEquipo;
use App\Persona;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Regresión de los 500 de producción relevados el 2026-07-30: relaciones null
 * no defendidas en vistas/controladores que reventaban en vez de degradar.
 */
class Produccion500sTest extends TestCase
{
    use RefreshDatabase;

    /**
     * #1 — La página pública de la actividad no debe reventar si un coordinador
     * apunta a una persona borrada (soft-deleted).
     *
     * @test
     */
    public function pagina_de_actividad_no_revienta_con_coordinador_sin_persona()
    {
        $actividad = factory('App\Actividad')->create();
        $coordinador = factory('App\Coordinador')->create(['idActividad' => $actividad->idActividad]);

        // La persona del coordinador se borra (soft delete) → la relación queda null.
        Persona::find($coordinador->idPersona)->delete();

        $this->get('/actividades/' . $actividad->idActividad)
            ->assertStatus(200);
    }

    /**
     * #3 — El listado público de actividades no debe reventar si no hay
     * HomeHeader configurado para el país.
     *
     * @test
     */
    public function listado_de_actividades_no_revienta_sin_home_header()
    {
        // Sin crear ningún HomeHeader.
        $this->get('/actividades')
            ->assertStatus(200);
    }

    /**
     * #2 — La ficha médica exige usuario autenticado: sin sesión válida debe
     * devolver 401 (no 500 por acceder a Auth::user()->idPersona con null).
     *
     * @test
     */
    public function ficha_medica_sin_autenticar_devuelve_401()
    {
        $this->postJson('/ajax/fichaMedica', ['confirma_datos' => 1])
            ->assertStatus(401);
    }

    /**
     * #5 — Comunidades por equipo inexistente: colección vacía, no 500 por
     * acceder a $equipo->idOficina con null.
     *
     * @test
     */
    public function comunidades_por_equipo_inexistente_devuelve_vacio()
    {
        $this->getJson('/ajax/comunidades/equipo/999999/')
            ->assertStatus(200)
            ->assertExactJson([]);
    }

    /**
     * #4 — Punto de encuentro de una actividad inexistente: 404 limpio, no 500
     * por pasar null a la vista.
     *
     * @test
     */
    public function punto_de_encuentro_de_actividad_inexistente_devuelve_404()
    {
        $this->get('/inscripciones/actividad/999999')
            ->assertStatus(404);
    }

    /**
     * #6 — Listado de equipos por oficina para un coordinador: la rama coordinador
     * hace join con `coordinadores_equipos` (que también tiene `created_at`), así que
     * el ORDER BY por `created_at` sin calificar reventaba con 1052 (columna ambigua).
     * Debe devolver 200, no 500.
     *
     * @test
     */
    public function listado_de_equipos_por_oficina_para_coordinador_no_revienta()
    {
        // Rol coordinador con `ver_backoffice` (gate accesoBackoffice). Se arma a mano
        // en vez de seed('PermisosSeeder') para no depender del estado global de permisos.
        $permiso = \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'ver_backoffice']);
        $rol = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'coordinador']);
        $rol->givePermissionTo($permiso);

        $coordinador = factory('App\Persona')->create();
        $coordinador->assignRole('coordinador');

        // Equipo en una oficina, con el coordinador vinculado en coordinadores_equipos.
        $equipo = factory('App\Equipo')->create();
        CoordinadorEquipo::create([
            'idPersona' => $coordinador->idPersona,
            'idEquipo'  => $equipo->idEquipo,
        ]);

        $this->actingAs($coordinador)
            ->getJson('/admin/ajax/equipos/oficina/' . $equipo->idOficina)
            ->assertStatus(200)
            ->assertJsonFragment(['idEquipo' => $equipo->idEquipo]);
    }

    /**
     * 2026-10-06 — El detalle JSON de una actividad sin provincia válida (legacy,
     * idProvincia=0) tiraba 500 en ActividadResource y el voluntario no podía
     * inscribirse. Debe devolver 200 con ubicacion null.
     *
     * @test
     */
    public function detalle_de_actividad_sin_provincia_no_revienta()
    {
        $actividad = factory('App\Actividad')->create();
        $actividad->idProvincia = 0;
        $actividad->save();

        $this->getJson('/ajax/actividades/' . $actividad->idActividad)
            ->assertStatus(200)
            ->assertJson(['data' => ['ubicacion' => null]]);
    }

    /**
     * 2026-10-06 — La ruta DELETE de reuniones de equipo existía pero el método
     * del controller no (BadMethodCallException → 500). Debe soft-borrar la
     * reunión, y solo si pertenece al equipo de la URL.
     *
     * @test
     */
    public function borrar_reunion_de_equipo_funciona_y_se_acota_al_equipo()
    {
        $admin = $this->adminBackoffice();
        $equipo = factory('App\Equipo')->create();
        $otroEquipo = factory('App\Equipo')->create();
        $reunion = \App\EquipoReunion::create([
            'idEquipo'   => $equipo->idEquipo,
            'nombre'     => 'Reunión',
            'fecha'      => '2026-10-06 10:00:00',
            'despliegue' => 'presencial',
        ]);

        $this->actingAs($admin)
            ->deleteJson('/admin/ajax/equipos/' . $otroEquipo->idEquipo . '/reuniones/' . $reunion->idReunion)
            ->assertStatus(404);
        $this->assertDatabaseHas('equipo_reunion', ['idReunion' => $reunion->idReunion, 'deleted_at' => null]);

        $this->actingAs($admin)
            ->deleteJson('/admin/ajax/equipos/' . $equipo->idEquipo . '/reuniones/' . $reunion->idReunion)
            ->assertStatus(200);
        $this->assertSoftDeleted('equipo_reunion', ['idReunion' => $reunion->idReunion]);
    }

    /**
     * 2026-10-06 — Los textos libres del informe de cierre eran VARCHAR(191):
     * un comentario largo daba "Data too long" (500) y no se podía guardar.
     *
     * @test
     */
    public function informe_de_cierre_acepta_textos_largos()
    {
        $admin = $this->adminBackoffice();
        $actividad = factory('App\Actividad')->create();
        $actividad->tipo->idCategoria = 1;
        $actividad->tipo->save();
        $admin->idPaisPermitido = $actividad->idPais;
        $admin->save();

        $largo = str_repeat('Todas las viviendas se terminaron en tiempo y forma. ', 20);

        $this->actingAs($admin)
            ->postJson('/admin/ajax/actividades/' . $actividad->idActividad . '/informe_cierre', [
                'programa'                => $largo,
                'soluciones_entregadas'   => $largo,
                'quienes_financiaron'     => $largo,
                'comentarios_adicionales' => $largo,
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('actividad_informe_cierre', [
            'idActividad'             => $actividad->idActividad,
            'comentarios_adicionales' => $largo,
        ]);
    }

    /**
     * 2026-10-06 — Actividad legacy con roles de texto libre (tags {text} sin id):
     * /confirmar daba "Array to string conversion" (500) y la persona no podía
     * inscribirse. Debe mostrar el texto del rol, y al inscribirse guardarlo plano.
     *
     * @test
     */
    public function confirmar_con_roles_legacy_sin_id_no_revienta_y_guarda_el_texto()
    {
        Mail::fake();
        \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'ver_backoffice']);
        $persona = factory('App\Persona')->create();
        $actividad = factory('App\Actividad')->create();
        $p = $actividad->puntosEncuentro()->save(factory('App\PuntoEncuentro')->make());

        $roles = json_encode([
            ['text' => 'V.T. Logística y Abastecimiento', 'tiClasses' => ['ti-valid']],
            ['id' => 'monitor', 'text' => 'Monitor/a'],
        ]);

        $this->actingAs($persona)
            ->post('/inscripciones/actividad/' . $actividad->idActividad . '/confirmar', [
                'punto_encuentro'         => $p->idPuntoEncuentro,
                'roles_aplicados'         => $roles,
                'inscripciones_aplicadas' => json_encode([['text' => 'Tipo libre']]),
                'jornadas'                => '[]',
            ])
            ->assertStatus(200)
            ->assertSeeText('V.T. Logística y Abastecimiento')
            ->assertSeeText('Tipo libre');

        $this->actingAs($persona)
            ->post('/inscripciones/actividad/' . $actividad->idActividad . '/gracias', [
                'punto_encuentro'  => $p->idPuntoEncuentro,
                'aceptar_terminos' => 1,
                'roles_aplicados'  => $roles,
            ])
            ->assertStatus(200);

        $inscripcion = \App\Inscripcion::where('idPersona', $persona->idPersona)->firstOrFail();
        $this->assertEquals(['V.T. Logística y Abastecimiento', 'monitor'], $inscripcion->roles_aplicados);
    }

    /**
     * Sin aceptar términos, /gracias re-renderiza la confirmación: antes faltaban las
     * variables de la vista (roles_aplicados, jornadas, edad…) → "Undefined variable".
     *
     * @test
     */
    public function gracias_sin_aceptar_terminos_vuelve_a_confirmar_sin_500()
    {
        \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'ver_backoffice']);
        $persona = factory('App\Persona')->create();
        $actividad = factory('App\Actividad')->create();
        $p = $actividad->puntosEncuentro()->save(factory('App\PuntoEncuentro')->make());

        $this->actingAs($persona)
            ->post('/inscripciones/actividad/' . $actividad->idActividad . '/gracias', [
                'punto_encuentro' => $p->idPuntoEncuentro,
                'roles_aplicados' => json_encode([['id' => 'monitor', 'text' => 'Monitor/a']]),
            ])
            ->assertStatus(200);
    }

    private function adminBackoffice()
    {
        $permiso = \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'ver_backoffice']);
        $rol = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin']);
        $rol->givePermissionTo($permiso);

        $admin = factory('App\Persona')->create();
        $admin->assignRole('admin');

        return $admin;
    }
}
