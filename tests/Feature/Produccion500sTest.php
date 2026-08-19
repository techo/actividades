<?php

namespace Tests\Feature;

use App\CoordinadorEquipo;
use App\Persona;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
