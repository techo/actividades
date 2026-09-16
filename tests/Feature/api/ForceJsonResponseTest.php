<?php

namespace Tests\Feature\api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * El middleware ForceJsonResponse (grupo `api`) garantiza que los errores de las
 * rutas API vuelvan como JSON aunque el cliente no mande `Accept: application/json`.
 * Sin él, un error se renderiza como redirect 302 (HTML) y la app no ve el mensaje.
 */
class ForceJsonResponseTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Ruta API protegida, sin token y SIN header Accept: debe dar 401 JSON, no un
     * redirect 302 a /login. (Se usa get(), no getJson(), justamente para NO mandar
     * el header y probar que el middleware lo fuerza.)
     *
     * @test
     */
    public function ruta_api_sin_token_devuelve_401_json_y_no_redirect()
    {
        $response = $this->get('/api/actividades');

        $response->assertStatus(401);
        $response->assertJson(['error' => 'Unauthenticated.']);
    }
}
