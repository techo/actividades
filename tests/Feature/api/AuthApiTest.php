<?php

namespace Tests\Feature\api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Laravel\Passport\ClientRepository;
use Tests\TestCase;

/**
 * Tarea #14 — Tests de la API mobile: autenticación y registro (PersonasController).
 * Puerta de entrada de la app MiTECHO. El campo de email es 'mail' (no 'email').
 */
class AuthApiTest extends TestCase
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
    public function login_con_credenciales_validas_devuelve_token_y_persona()
    {
        factory('App\Persona')->create([
            'mail'     => 'voluntario@techo.org',
            'password' => bcrypt('secret123'),
        ]);

        $this->postJson('/api/login', [
            'mail'     => 'voluntario@techo.org',
            'password' => 'secret123',
        ])
            ->assertStatus(200)
            ->assertJson([ 'success' => true ])
            ->assertJsonStructure([ 'success', 'user' => ['idPersona', 'mail'], 'mensaje', 'token' ]);
    }

    /** @test */
    public function login_con_credenciales_invalidas_devuelve_401()
    {
        factory('App\Persona')->create([
            'mail'     => 'voluntario@techo.org',
            'password' => bcrypt('secret123'),
        ]);

        $this->postJson('/api/login', [
            'mail'     => 'voluntario@techo.org',
            'password' => 'incorrecta',
        ])
            ->assertStatus(401)
            ->assertJson([ 'success' => false ]);
    }

    /** @test */
    public function register_con_datos_validos_crea_persona_y_devuelve_token()
    {
        Notification::fake();
        $pais = factory('App\Pais')->create();

        $this->postJson('/api/register', $this->payloadRegistro($pais->id))
            ->assertStatus(201)
            ->assertJson([ 'success' => true ])
            ->assertJsonStructure([ 'success', 'persona' => ['idPersona', 'mail'], 'mensaje', 'token' ]);

        $this->assertDatabaseHas('Persona', [ 'mail' => 'nuevo@techo.org' ]);
    }

    /** @test */
    public function register_con_email_duplicado_falla_validacion()
    {
        $pais = factory('App\Pais')->create();
        factory('App\Persona')->create([ 'mail' => 'nuevo@techo.org' ]);

        $this->postJson('/api/register', $this->payloadRegistro($pais->id))
            ->assertStatus(422)
            ->assertJsonValidationErrors('mail');
    }

    /** @test */
    public function provider_login_sin_provider_o_token_falla_validacion()
    {
        $this->postJson('/api/providerLogin', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['provider', 'token']);
    }

    /** @test */
    public function provider_login_con_proveedor_invalido_devuelve_400()
    {
        $this->postJson('/api/providerLogin', [ 'provider' => 'desconocido', 'token' => 'x' ])
            ->assertStatus(400)
            ->assertJson([ 'success' => false ]);
    }

    private function payloadRegistro($idPais): array
    {
        return [
            'mail'                   => 'nuevo@techo.org',
            'password'               => 'secret123',
            'password_confirmation'  => 'secret123',
            'nombres'                => 'Camila',
            'apellidoPaterno'        => 'Perez',
            'fechaNacimiento'        => '1995-05-05',
            'telefono'               => 1145678901,
            'telefonoMovil'          => 1145678901,
            'dni'                    => 33444555,
            'recibirMails'           => 1,
            'acepta_marketing'       => 1,
            'idPais'                 => $idPais,
            'idProvincia'            => 1,
            'idLocalidad'            => 1,
            'idUnidadOrganizacional' => 0,
        ];
    }
}
