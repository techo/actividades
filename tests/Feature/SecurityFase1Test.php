<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Regresión de la Fase 1 de hardening.
 *
 * A-8: el middleware `requiere.auth` no autenticaba (solo seteaba un flag de vista).
 * Los endpoints que operan sobre datos del usuario autenticado ahora exigen sesión real
 * (redirigen a /login para un invitado), sin afectar las rutas públicas de registro/login.
 */
class SecurityFase1Test extends TestCase
{
    use RefreshDatabase;

    /**
     * Endpoints privados que antes eran alcanzables sin sesión.
     *
     * @test
     * @dataProvider rutasPrivadas
     */
    public function endpoint_privado_requiere_autenticacion($metodo, $uri)
    {
        $this->{$metodo}($uri)->assertRedirect('/login');
    }

    public function rutasPrivadas()
    {
        return [
            'perfil'                 => ['get', '/ajax/usuario/perfil'],
            'mis inscripciones'      => ['get', '/ajax/usuario/inscripciones'],
            'ficha médica'           => ['post', '/ajax/fichaMedica'],
            'estudios del usuario'   => ['get', '/ajax/estudios/usuario'],
            'voucher de pago'        => ['post', '/ajax/inscripcion/voucherPago'],
            'solicitud de beca'      => ['post', '/ajax/inscripcion/becaSolicitud'],
            'pregunta archivo'       => ['post', '/ajax/inscripcion/pregunta-archivo'],
        ];
    }

    /**
     * Las rutas públicas de registro/validación NO deben exigir sesión.
     *
     * @test
     */
    public function ruta_publica_de_validacion_sigue_accesible_sin_sesion()
    {
        // No debe redirigir a /login (es parte del flujo de registro).
        $response = $this->get('/ajax/usuario/validar/create?mail=nuevo@ejemplo.com');
        $this->assertNotEquals(302, $response->getStatusCode());
    }
}
