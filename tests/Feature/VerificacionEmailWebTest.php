<?php

namespace Tests\Feature;

use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/**
 * Tarea #46 (upgrade-fase0) — Flujo de verificación de email.
 *
 * BLOQUEANTE de Fase 1. Persona autentica por el campo `mail` (no `email`), y desde
 * Laravel 6 el flujo por defecto de VerifiesEmails valida un {hash} = sha1(email),
 * que sobre Persona daría null (ver upgrade-review.md §2.3). El proyecto ya reescribió
 * VerificationController@verify para verificar por `id` + URL firmada (middleware
 * 'signed'), sin depender de {hash} ni de sesión.
 *
 * Este test fija ese comportamiento actual (en 5.7) como ancla: al subir a L6 debe
 * seguir verde. Si se rompe, es la señal de que el flujo volvió a depender del
 * mecanismo por defecto incompatible con el campo `mail`.
 */
class VerificacionEmailWebTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function un_link_firmado_valido_marca_el_email_como_verificado_sin_sesion()
    {
        Event::fake();

        $persona = factory('App\Persona')->create([
            'email_verified_at' => null,
            'registro_origen'   => 'web',
        ]);

        $this->assertFalse($persona->hasVerifiedEmail());

        // Mismo mecanismo que app/Notifications/VerifyEmail@verificationUrl.
        $url = URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(1440),
            ['id' => $persona->idPersona]
        );

        // Sin autenticar: la seguridad del link es la firma, no la sesión.
        $this->get($url)->assertRedirect('/');

        $this->assertTrue($persona->fresh()->hasVerifiedEmail());
        Event::assertDispatched(Verified::class);
    }

    /** @test */
    public function un_link_sin_firma_valida_es_rechazado_y_no_verifica()
    {
        $persona = factory('App\Persona')->create([
            'email_verified_at' => null,
        ]);

        // Ruta correcta pero SIN la firma → el middleware 'signed' devuelve 403.
        $this->get('/email/verify/' . $persona->idPersona)
            ->assertStatus(403);

        $this->assertFalse($persona->fresh()->hasVerifiedEmail());
    }
}
