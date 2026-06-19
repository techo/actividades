<?php

namespace Tests\Feature\api;

use App\Dispositivo;
use App\Jobs\EnviarNotificacionPush;
use App\Services\Push\PushNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Laravel\Passport\Passport;
use Tests\TestCase;

/**
 * Tarea #17 — Tests de la API mobile: registro de dispositivos y push.
 * DispositivoController registra tokens de OneSignal (upsert por player_id).
 * Si falla, los voluntarios dejan de recibir notificaciones.
 */
class DispositivosApiTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function registrar_guarda_el_dispositivo_del_usuario()
    {
        $persona = factory('App\Persona')->create();
        Passport::actingAs($persona);

        $this->postJson('/api/dispositivos', [
            'player_id'  => 'ONESIGNAL-ABC',
            'plataforma' => 'android',
        ])
            ->assertStatus(200)
            ->assertJson([ 'success' => true ]);

        $this->assertDatabaseHas('Dispositivo', [
            'player_id'  => 'ONESIGNAL-ABC',
            'idPersona'  => $persona->idPersona,
            'plataforma' => 'android',
            'activo'     => 1,
        ]);
    }

    /** @test */
    public function registrar_un_player_id_existente_lo_reasigna_sin_duplicar()
    {
        $persona = factory('App\Persona')->create();
        $otra    = factory('App\Persona')->create();
        Passport::actingAs($persona);

        // El player_id ya existía para otra persona y desactivado (cambio de teléfono).
        Dispositivo::create([
            'idPersona'  => $otra->idPersona,
            'player_id'  => 'ONESIGNAL-ABC',
            'plataforma' => 'ios',
            'activo'     => false,
        ]);

        $this->postJson('/api/dispositivos', [
            'player_id'  => 'ONESIGNAL-ABC',
            'plataforma' => 'android',
        ])->assertStatus(200);

        $this->assertEquals(1, Dispositivo::where('player_id', 'ONESIGNAL-ABC')->count());
        $this->assertDatabaseHas('Dispositivo', [
            'player_id' => 'ONESIGNAL-ABC',
            'idPersona' => $persona->idPersona,
            'activo'    => 1,
        ]);
    }

    /** @test */
    public function registrar_sin_autenticacion_devuelve_401()
    {
        $this->postJson('/api/dispositivos', [ 'player_id' => 'X' ])
            ->assertStatus(401);
    }

    /** @test */
    public function el_servicio_de_push_despacha_el_job_cuando_hay_dispositivo_activo()
    {
        Queue::fake();

        $persona = factory('App\Persona')->create([ 'recibir_push' => 1 ]);
        Dispositivo::create([
            'idPersona' => $persona->idPersona,
            'player_id' => 'ONESIGNAL-ABC',
            'activo'    => true,
        ]);

        app(PushNotificationService::class)->enviar($persona, 'Título', 'Mensaje', ['tipo' => 'test']);

        Queue::assertPushed(EnviarNotificacionPush::class);
    }

    /** @test */
    public function el_servicio_de_push_no_despacha_si_el_usuario_no_recibe_push()
    {
        Queue::fake();

        $persona = factory('App\Persona')->create([ 'recibir_push' => 0 ]);
        Dispositivo::create([
            'idPersona' => $persona->idPersona,
            'player_id' => 'ONESIGNAL-ABC',
            'activo'    => true,
        ]);

        app(PushNotificationService::class)->enviar($persona, 'Título', 'Mensaje');

        Queue::assertNotPushed(EnviarNotificacionPush::class);
    }
}
