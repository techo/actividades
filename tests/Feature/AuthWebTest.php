<?php

namespace Tests\Feature;

use App\Mail\ForgotPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/**
 * Tarea #10 — Tests web: flujos de autenticación.
 * Login/logout por formulario, reset de password, verificación de email y
 * baja de mailing. El campo de email es 'mail' (no 'email').
 */
class AuthWebTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function login_con_credenciales_validas_devuelve_success()
    {
        factory('App\Persona')->create([
            'mail'     => 'voluntario@techo.org',
            'password' => bcrypt('secret123'),
        ]);

        $this->postJson('/login', [
            'mail'     => 'voluntario@techo.org',
            'password' => 'secret123',
        ])
            ->assertStatus(200)
            ->assertJson([ 'success' => true ])
            ->assertSessionHas('pais');   // login fija el país/locale del usuario
    }

    /** @test */
    public function login_con_credenciales_invalidas_devuelve_403()
    {
        factory('App\Persona')->create([
            'mail'     => 'voluntario@techo.org',
            'password' => bcrypt('secret123'),
        ]);

        $this->postJson('/login', [
            'mail'     => 'voluntario@techo.org',
            'password' => 'incorrecta',
        ])
            ->assertStatus(403)
            ->assertJson([ 'success' => false ]);
    }

    /** @test */
    public function logout_destruye_la_sesion()
    {
        $persona = factory('App\Persona')->create();

        $this->actingAs($persona)
            ->postJson('/logout')
            ->assertStatus(200)
            ->assertJson([ 'success' => true ]);

        $this->assertGuest();
    }

    /** @test */
    public function pedir_reset_de_password_encola_el_mail()
    {
        Mail::fake();

        factory('App\Persona')->create([ 'mail' => 'voluntario@techo.org' ]);

        $this->post('/password/email', [ 'mail' => 'voluntario@techo.org' ]);

        Mail::assertSent(ForgotPassword::class);
    }

    /** @test */
    public function verificar_email_marca_la_persona_como_verificada()
    {
        $persona = factory('App\Persona')->create([ 'email_verified_at' => null ]);

        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $persona->idPersona]
        );

        $this->actingAs($persona)->get($url);

        $this->assertNotNull($persona->fresh()->email_verified_at);
    }

    /** @test */
    public function desuscribirse_baja_a_la_persona_del_mailing()
    {
        $persona = factory('App\Persona')->create([
            'mail'              => 'voluntario@techo.org',
            'recibirMails'      => 1,
            'unsubscribe_token' => 'token-uuid-123',
        ]);

        $this->get('/desuscribirse/token-uuid-123')->assertStatus(200);

        $this->post('/desuscribirse/token-uuid-123', [ 'mail' => 'voluntario@techo.org' ])
            ->assertStatus(200);

        $this->assertEquals(0, $persona->fresh()->recibirMails);
    }
}
