<?php

namespace Tests\Feature;

use App\Campaign;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tarea #13 — Tests web: campañas y suscripciones.
 * Rutas bajo el prefijo {abreviacion} (middleware UrlPais). Captación de
 * voluntarios vía Campaign + Suscribe (tabla legacy Suscripciones).
 */
class CampaniasWebTest extends TestCase
{
    use RefreshDatabase;

    private function paisAr()
    {
        return factory('App\Pais')->create([ 'abreviacion' => 'ar', 'habilitado' => 1 ]);
    }

    private function campaniaActiva($paisId, $activa = true)
    {
        return Campaign::create([
            'nombre'  => 'Colecta 2026',
            'tipo'    => 'colecta',
            'pais_id' => $paisId,
            'activa'  => $activa,
        ]);
    }

    /** @test */
    public function una_campania_activa_es_accesible()
    {
        $pais     = $this->paisAr();
        $campania = $this->campaniaActiva($pais->id);

        $this->get("/ar/campania/{$campania->id}")
            ->assertStatus(200);
    }

    /** @test */
    public function una_campania_inactiva_devuelve_404()
    {
        $pais     = $this->paisAr();
        $campania = $this->campaniaActiva($pais->id, false);

        $this->get("/ar/campania/{$campania->id}")
            ->assertStatus(404);
    }

    /** @test */
    public function suscribirse_a_una_campania_persiste_la_suscripcion()
    {
        $pais     = $this->paisAr();
        $campania = $this->campaniaActiva($pais->id);

        $this->post('/ar/suscribe', [
            'campaign_id' => $campania->id,
            'mail'        => 'aspirante@techo.org',
            'nombre'      => 'Ana',
            'apellido'    => 'Pérez',
        ])
            ->assertJson([ 'success' => true ]);

        $this->assertDatabaseHas('Suscripciones', [
            'mail'        => 'aspirante@techo.org',
            'campaign_id' => $campania->id,
        ]);
    }

    /** @test */
    public function suscribirse_dos_veces_a_la_misma_campania_no_da_error_500()
    {
        $pais     = $this->paisAr();
        $campania = $this->campaniaActiva($pais->id);

        $datos = [
            'campaign_id' => $campania->id,
            'mail'        => 'aspirante@techo.org',
            'nombre'      => 'Ana',
            'apellido'    => 'Pérez',
        ];

        $this->post('/ar/suscribe', $datos)->assertJson([ 'success' => true ]);

        // El segundo intento se rechaza con 422 (already_registered), no con 500.
        $this->post('/ar/suscribe', $datos)
            ->assertStatus(422)
            ->assertJson([ 'already_registered' => true ]);

        $this->assertEquals(1, \App\Suscribe::where('campaign_id', $campania->id)
            ->where('mail', 'aspirante@techo.org')->count());
    }
}
