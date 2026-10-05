<?php

namespace Tests\Feature;

use App\Persona;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Task 50 — la baja de cuenta corta todo acceso (reclamo #15: una cuenta "eliminada" se
 * auto-inscribió días después con la sesión "recordarme" / tokens de otros dispositivos).
 */
class BajaCuentaCortaAccesoTest extends TestCase
{
    use RefreshDatabase;

    private function crearClientePassport()
    {
        app(\Laravel\Passport\ClientRepository::class)->createPersonalAccessClient(
            null, 'Test Personal Access Client', config('app.url') ?: 'http://localhost'
        );
    }

    /** @test */
    public function al_borrar_la_cuenta_se_invalidan_password_recordarme_social_y_todos_los_tokens()
    {
        $this->crearClientePassport();
        $persona = factory(Persona::class)->create([
            'password' => Hash::make('claveVieja123'),
            'google_id' => 'g-123',
            'facebook_id' => 'f-123',
            'remember_token' => 'recordarme-viejo',
        ]);
        $persona->createToken('celular');
        $persona->createToken('tablet');

        $this->actingAs($persona)->delete('/ajax/usuario')->assertStatus(302);

        $persona = $persona->fresh();
        $this->assertEquals('Usuario eliminado', $persona->nombres);
        $this->assertFalse(Hash::check('claveVieja123', $persona->password));
        $this->assertNotEquals('recordarme-viejo', $persona->remember_token);
        $this->assertNull($persona->google_id);
        $this->assertNull($persona->facebook_id);
        $this->assertEquals(0, $persona->tokens()->where('revoked', false)->count());
    }

    /** @test */
    public function comando_corta_acceso_de_cuentas_ya_anonimizadas_solo_con_commit()
    {
        $this->crearClientePassport();
        $anonimizada = factory(Persona::class)->create([
            'nombres' => 'Usuario eliminado', 'estadoPersona' => 'Desvinculado',
            'remember_token' => 'recordarme-viejo', 'google_id' => 'g-1',
        ]);
        $anonimizada->createToken('celular');
        $activa = factory(Persona::class)->create(['remember_token' => 'intacto']);

        $this->artisan('personas:cortar-acceso-anonimizadas')->assertExitCode(0);
        $this->assertEquals('recordarme-viejo', $anonimizada->fresh()->remember_token);

        $this->artisan('personas:cortar-acceso-anonimizadas', ['--commit' => true])->assertExitCode(0);
        $this->assertNotEquals('recordarme-viejo', $anonimizada->fresh()->remember_token);
        $this->assertNull($anonimizada->fresh()->google_id);
        $this->assertEquals(0, $anonimizada->tokens()->where('revoked', false)->count());
        $this->assertEquals('intacto', $activa->fresh()->remember_token);
    }
}
