<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Task 32 — Endpoint de health-check.
 *
 * GET /health sin auth: 200 con estado de BD cuando todo está sano, apto para
 * uptime monitoring. La ruta vive fuera de los grupos web/api (RouteServiceProvider),
 * por eso no necesita sesión, CSRF ni país.
 */
class HealthCheckTest extends TestCase
{
    /** @test */
    public function health_devuelve_200_con_estado_de_bd_ok()
    {
        $response = $this->getJson('/health');

        $response->assertStatus(200)
            ->assertJson([
                'status'   => 'ok',
                'database' => 'ok',
            ])
            ->assertJsonStructure(['status', 'app', 'database']);
    }

    /** @test */
    public function health_no_requiere_autenticacion()
    {
        // Sin actingAs ni token: debe responder igual.
        $this->getJson('/health')->assertStatus(200);
    }

    /** @test */
    public function health_no_se_cachea()
    {
        $response = $this->getJson('/health');

        $this->assertContains(
            'no-store',
            $response->headers->get('Cache-Control')
        );
    }
}
