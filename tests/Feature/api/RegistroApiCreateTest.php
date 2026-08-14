<?php

namespace Tests\Feature\api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Laravel\Passport\ClientRepository;
use Tests\TestCase;

/**
 * Regresión — registro directo por la API mobile: POST /api/create
 * (ajax\UsuarioController@apiCreate).
 *
 * Este es el endpoint que consumen los clientes reales (app + Postman), distinto
 * del /api/register (PersonasController) que ya cubre AuthApiTest. Quedó sin tests
 * y por eso pasó desapercibido que socialVerificado() hacía $request->session()
 * en una ruta STATELESS (el grupo de middleware 'api' no incluye StartSession),
 * rompiendo el 100% de los registros con "Session store not set on request" (500).
 *
 * La clave de esta regresión: postJson() sobre una ruta 'api' NO tiene sesión,
 * igual que en producción. Sin el fix (guard con hasSession()) estos tests dan 500.
 *
 * Contrato legacy de /api/create: email, pass, nombre, apellido, pais, privacidad
 * (NO mail/password/nombres como /api/register).
 */
class RegistroApiCreateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Passport necesita un personal access client para que createToken() funcione.
        app(ClientRepository::class)->createPersonalAccessClient(
            null,
            'Test Personal Access Client',
            config('app.url') ?: 'http://localhost'
        );
    }

    /** @test */
    public function registro_directo_sin_sesion_crea_persona_y_devuelve_token()
    {
        Notification::fake();
        $pais = factory('App\Pais')->create();

        // Ruta stateless (sin sesión): reproduce la condición que disparaba el 500.
        $this->postJson('/api/create', $this->payload($pais->id))
            ->assertStatus(200)
            ->assertJsonStructure(['mensaje', 'token', 'persona' => ['idPersona', 'mail'], 'abreviacionPais'])
            ->assertJson(['loginSocial' => false]);

        $this->assertDatabaseHas('Persona', ['mail' => 'nuevo.api@techo.org']);
    }

    /** @test */
    public function registro_con_provider_invalido_no_es_500_y_cae_a_registro_plano()
    {
        Notification::fake();
        $pais = factory('App\Pais')->create();

        // Con provider/token el flujo entra a socialVerificado(): sin sesión + proveedor
        // desconocido debe resolverse como registro plano (no reventar). Antes del fix,
        // ni siquiera llegaba a evaluar el proveedor: moría en $request->session().
        $payload = $this->payload($pais->id, ['provider' => 'desconocido', 'token' => 'xxx']);

        $this->postJson('/api/create', $payload)
            ->assertStatus(200)
            ->assertJson(['loginSocial' => false]);
    }

    /** @test */
    public function registro_con_email_duplicado_devuelve_422_no_500()
    {
        $pais = factory('App\Pais')->create();
        factory('App\Persona')->create(['mail' => 'nuevo.api@techo.org']);

        $this->postJson('/api/create', $this->payload($pais->id))
            ->assertStatus(422)
            ->assertJsonValidationErrors('email');
    }

    private function payload($idPais, array $overrides = []): array
    {
        return array_merge([
            'nombre'           => 'Camila',
            'apellido'         => 'Perez',
            'email'            => 'nuevo.api@techo.org',
            'pass'             => 'secret123',
            'telefono'         => '+541145678901',
            'pais'             => $idPais,
            'privacidad'       => 1,
            'acepta_marketing' => 0,
        ], $overrides);
    }
}
