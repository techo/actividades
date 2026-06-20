<?php

namespace Tests\Feature;

use App\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Tarea #11 — Tests web: perfil del voluntario.
 * El área /perfil requiere usuario autenticado y con email verificado.
 */
class PerfilWebTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // El header.blade.php chequea hasPermissionTo('ver_backoffice'), que lanza
        // excepción si el permiso no existe. En producción está sembrado.
        $this->seed('PermisosSeeder');
    }

    /** @test */
    public function el_perfil_es_accesible_para_usuario_autenticado_y_verificado()
    {
        $persona = factory('App\Persona')->create();   // el factory deja email_verified_at seteado

        $this->actingAs($persona)
            ->get('/perfil/')
            ->assertStatus(200);
    }

    /** @test */
    public function el_perfil_redirige_si_no_esta_autenticado()
    {
        $this->get('/perfil/')
            ->assertStatus(302);
    }

    /** @test */
    public function las_actividades_del_perfil_son_accesibles()
    {
        $persona = factory('App\Persona')->create();

        $this->actingAs($persona)
            ->get('/perfil/actividades')
            ->assertStatus(200);
    }

    /** @test */
    public function actualizar_email_cambia_el_mail_y_pide_reverificacion()
    {
        Notification::fake();

        $persona = factory('App\Persona')->create([ 'mail' => 'viejo@techo.org' ]);

        $this->actingAs($persona)
            ->post('/perfil/actualizar_email', [
                'email'              => 'nuevo@techo.org',
                'email_confirmation' => 'nuevo@techo.org',
            ])
            ->assertRedirect('/');

        $persona->refresh();
        $this->assertEquals('nuevo@techo.org', $persona->mail);
        $this->assertNull($persona->email_verified_at);   // queda pendiente de reverificar

        Notification::assertSentTo($persona, VerifyEmail::class);
    }
}
