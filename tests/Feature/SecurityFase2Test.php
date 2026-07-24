<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

/**
 * Regresión de la Fase 2 de hardening (authz / aislamiento tenant / mass assignment).
 */
class SecurityFase2Test extends TestCase
{
    use RefreshDatabase;

    /**
     * M-1: un usuario de backoffice no puede leer la PII de una persona de otro país.
     *
     * @test
     */
    public function no_se_puede_leer_persona_de_otro_pais()
    {
        $this->seed('PermisosSeeder');

        $paisA = factory('App\Pais')->create();
        $paisB = factory('App\Pais')->create();

        $coord = factory('App\Persona')->create([
            'idPais' => $paisA->id, 'idPaisPermitido' => $paisA->id,
        ]);
        $coord->assignRole('coordinador'); // tiene ver_usuarios

        $mismoPais = factory('App\Persona')->create(['idPais' => $paisA->id]);
        $otroPais  = factory('App\Persona')->create(['idPais' => $paisB->id]);

        $this->actingAs($coord)
            ->get('/admin/ajax/personas/'.$mismoPais->idPersona)
            ->assertStatus(200);

        $this->actingAs($coord)
            ->get('/admin/ajax/personas/'.$otroPais->idPersona)
            ->assertStatus(404);
    }

    /**
     * M-3: un actor no-admin (aunque tenga asignar_roles) no puede otorgar el rol admin
     * ni asignar roles a usuarios de otro país.
     *
     * @test
     */
    public function no_admin_no_puede_elevar_a_admin_ni_cruzar_pais()
    {
        $this->seed('PermisosSeeder');

        $paisA = factory('App\Pais')->create();
        $paisB = factory('App\Pais')->create();

        $actor = factory('App\Persona')->create([
            'idPais' => $paisA->id, 'idPaisPermitido' => $paisA->id,
        ]);
        // ver_backoffice para pasar el grupo /admin; asignar_roles para la ruta. NO es admin.
        $actor->givePermissionTo(['asignar_roles', 'ver_backoffice']);
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $targetA = factory('App\Persona')->create(['idPais' => $paisA->id]);
        $targetB = factory('App\Persona')->create(['idPais' => $paisB->id]);

        $rolAdmin       = Role::findByName('admin');
        $rolCoordinador = Role::findByName('coordinador');

        // No puede elevar a admin.
        $this->actingAs($actor)
            ->post('/admin/roles/usuario/'.$targetA->idPersona, ['rolID' => $rolAdmin->id])
            ->assertStatus(403);

        // No puede asignar roles a un usuario de otro país.
        $this->actingAs($actor)
            ->post('/admin/roles/usuario/'.$targetB->idPersona, ['rolID' => $rolCoordinador->id])
            ->assertStatus(403);

        // Sí puede asignar un rol no-admin a un usuario de su país.
        $this->actingAs($actor)
            ->post('/admin/roles/usuario/'.$targetA->idPersona, ['rolID' => $rolCoordinador->id])
            ->assertStatus(200);
    }

    /**
     * M-15: un usuario no puede auto-modificar su estadoPersona vía el self-update.
     *
     * @test
     */
    public function usuario_no_puede_cambiar_su_propio_estado()
    {
        $persona = factory('App\Persona')->create(['estadoPersona' => 'Suspendido']);

        $this->actingAs($persona)
            ->put('/ajax/usuario', ['estadoPersona' => 'Activo']);

        $persona->refresh();
        $this->assertEquals('Suspendido', $persona->estadoPersona);
    }
}
