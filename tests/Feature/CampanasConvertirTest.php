<?php

namespace Tests\Feature;

use App\Persona;
use App\Suscribe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * `CampanasController::convertir()` (conversión de un lead de captación en
 * `Persona`) no chequeaba si ya existía una cuenta con ese mail antes de
 * crear — ni siquiera de forma secuencial, no hacía falta ninguna
 * concurrencia. Ver auditoría en progress/auditoria-duplicados-personas-emails.md.
 */
class CampanasConvertirTest extends TestCase
{
    use RefreshDatabase;

    private function admin($idPaisPermitido)
    {
        $this->seed('PermisosSeeder');

        $admin = factory('App\Persona')->create(['idPaisPermitido' => $idPaisPermitido]);
        $admin->assignRole('admin');

        return $admin;
    }

    private function suscripcion($idPais, array $overrides = [])
    {
        return Suscribe::create(array_merge([
            'mail'      => 'lead@techo.org',
            'nombre'    => 'Lead',
            'apellido'  => 'Captado',
            'telefono'  => '+541145678901',
            'idPais'    => $idPais,
            'convertido' => false,
        ], $overrides));
    }

    /** @test */
    public function convertir_crea_una_persona_nueva_si_no_existe_ninguna_con_ese_mail()
    {
        $pais = factory('App\Pais')->create();
        $admin = $this->admin($pais->id);
        $suscripcion = $this->suscripcion($pais->id);

        $this->actingAs($admin)
            ->post("/admin/ajax/campanas/{$suscripcion->id}/convertir")
            ->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('Persona', ['mail' => 'lead@techo.org']);
        $this->assertDatabaseHas('Suscripciones', ['id' => $suscripcion->id, 'convertido' => 1]);
        $this->assertEquals(1, Persona::where('mail', 'lead@techo.org')->count());
    }

    /** @test */
    public function convertir_vincula_a_la_persona_activa_existente_en_vez_de_duplicarla()
    {
        $pais = factory('App\Pais')->create();
        $admin = $this->admin($pais->id);

        $existente = factory('App\Persona')->create([
            'mail'   => 'lead@techo.org',
            'idPais' => $pais->id,
        ]);

        $suscripcion = $this->suscripcion($pais->id);

        $this->actingAs($admin)
            ->post("/admin/ajax/campanas/{$suscripcion->id}/convertir")
            ->assertStatus(200)
            ->assertJson(['success' => true, 'idPersona' => $existente->idPersona]);

        $this->assertEquals(1, Persona::where('mail', 'lead@techo.org')->count());
        $this->assertDatabaseHas('Suscripciones', [
            'id'        => $suscripcion->id,
            'convertido' => 1,
            'idPersona' => $existente->idPersona,
        ]);
    }

    /** @test */
    public function convertir_restaura_una_persona_borrada_en_vez_de_duplicarla()
    {
        $pais = factory('App\Pais')->create();
        $admin = $this->admin($pais->id);

        $borrada = factory('App\Persona')->create([
            'mail'   => 'lead@techo.org',
            'idPais' => $pais->id,
        ]);
        $borrada->delete();
        $this->assertSoftDeleted('Persona', ['idPersona' => $borrada->idPersona]);

        $suscripcion = $this->suscripcion($pais->id);

        $this->actingAs($admin)
            ->post("/admin/ajax/campanas/{$suscripcion->id}/convertir")
            ->assertStatus(200)
            ->assertJson(['success' => true, 'idPersona' => $borrada->idPersona]);

        $this->assertDatabaseHas('Persona', ['idPersona' => $borrada->idPersona, 'deleted_at' => null]);
        $this->assertEquals(1, Persona::withTrashed()->where('mail', 'lead@techo.org')->count());
    }

    /** @test */
    public function convertir_dos_veces_el_mismo_lead_no_duplica()
    {
        $pais = factory('App\Pais')->create();
        $admin = $this->admin($pais->id);
        $suscripcion = $this->suscripcion($pais->id);

        $this->actingAs($admin)->post("/admin/ajax/campanas/{$suscripcion->id}/convertir")
            ->assertStatus(200);

        $this->actingAs($admin)->post("/admin/ajax/campanas/{$suscripcion->id}/convertir")
            ->assertStatus(422)
            ->assertJson(['message' => 'Ya fue convertido en usuario.']);

        $this->assertEquals(1, Persona::where('mail', 'lead@techo.org')->count());
    }
}
