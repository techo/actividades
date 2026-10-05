<?php

namespace Tests\Feature;

use App\Persona;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Task 51 — buscador de personas del backoffice (/ajax/coordinadores), usado al agregar
 * integrantes a un equipo, coordinadores, responsables, etc. (reclamo #16).
 */
class BuscadorCoordinadoresTest extends TestCase
{
    use RefreshDatabase;

    private function coordinador($idPais)
    {
        $this->seed('PermisosSeeder');
        $c = factory(Persona::class)->create(['idPaisPermitido' => $idPais]);
        $c->assignRole('coordinador');
        return $c;
    }

    private function ids($response)
    {
        return collect($response->json('data') ?? $response->json())->pluck('idPersona')->all();
    }

    /** @test */
    public function por_email_exacto_encuentra_personas_de_otro_pais()
    {
        $mx = factory('App\Pais')->create();
        $latam = factory('App\Pais')->create();
        $coord = $this->coordinador($mx->id);
        $frida = factory(Persona::class)->create(['idPais' => $latam->id, 'mail' => 'frida@example.org', 'nombres' => 'Frida']);

        $r = $this->actingAs($coord)->getJson('/ajax/coordinadores?coordinador=' . urlencode('frida@example.org'))->assertStatus(200);
        $this->assertEquals([$frida->idPersona], $this->ids($r));

        // Por nombre se mantiene el aislamiento por país.
        $r = $this->actingAs($coord)->getJson('/ajax/coordinadores?coordinador=Frida')->assertStatus(200);
        $this->assertEquals([], $this->ids($r));
    }

    /** @test */
    public function la_busqueda_por_nombre_no_falla_con_campos_null()
    {
        $mx = factory('App\Pais')->create();
        $coord = $this->coordinador($mx->id);
        $p = factory(Persona::class)->create(['idPais' => $mx->id, 'nombres' => 'Daniela', 'apellidoPaterno' => 'Mostalac', 'dni' => null]);

        $r = $this->actingAs($coord)->getJson('/ajax/coordinadores?coordinador=' . urlencode('Daniela Mostalac'))->assertStatus(200);
        $this->assertEquals([$p->idPersona], $this->ids($r));
    }
}
