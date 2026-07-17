<?php

namespace Tests\Feature\api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;

/**
 * Tareas #27 y #28 (Etapa 0, seguridad-critica) — regresión de los IDOR en
 * api\PersonasController@show y @update: un usuario autenticado solo puede
 * leer y editar su propia Persona.
 */
class PerfilApiTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function un_usuario_puede_ver_su_propio_perfil()
    {
        $persona = factory('App\Persona')->create();
        Passport::actingAs($persona);

        $this->getJson('/api/personas/' . $persona->idPersona)
            ->assertStatus(200)
            ->assertJson([ 'idPersona' => $persona->idPersona, 'mail' => $persona->mail ]);
    }

    /** @test */
    public function un_usuario_no_puede_ver_el_perfil_de_otro()
    {
        $persona = factory('App\Persona')->create();
        $otra    = factory('App\Persona')->create();
        Passport::actingAs($persona);

        $this->getJson('/api/personas/' . $otra->idPersona)
            ->assertStatus(403);
    }

    /** @test */
    public function un_usuario_puede_actualizar_su_propio_perfil()
    {
        $persona = factory('App\Persona')->create();
        Passport::actingAs($persona);

        $this->postJson('/api/editPersona/' . $persona->idPersona, $this->payloadEdicion($persona))
            ->assertStatus(200)
            ->assertJson([ 'success' => true ]);

        $this->assertDatabaseHas('Persona', [
            'idPersona' => $persona->idPersona,
            'nombres'   => 'NombreEditado',
        ]);
    }

    /** @test */
    public function un_usuario_no_puede_actualizar_el_perfil_de_otro()
    {
        $persona = factory('App\Persona')->create();
        $otra    = factory('App\Persona')->create();
        Passport::actingAs($persona);

        $payload         = $this->payloadEdicion($otra);
        $payload['mail'] = 'atacante@evil.com';

        $this->postJson('/api/editPersona/' . $otra->idPersona, $payload)
            ->assertStatus(403);

        // Los datos de la víctima quedan intactos (el mail no fue sobrescrito).
        $this->assertDatabaseHas('Persona', [
            'idPersona' => $otra->idPersona,
            'mail'      => $otra->mail,
        ]);
        $this->assertDatabaseMissing('Persona', [ 'mail' => 'atacante@evil.com' ]);
    }

    /** @test */
    public function el_perfil_requiere_autenticacion()
    {
        $persona = factory('App\Persona')->create();

        $this->getJson('/api/personas/' . $persona->idPersona)->assertStatus(401);
        $this->postJson('/api/editPersona/' . $persona->idPersona, [])->assertStatus(401);
    }

    private function payloadEdicion($persona): array
    {
        return [
            'mail'                   => $persona->mail,
            'nombres'                => 'NombreEditado',
            'apellidoPaterno'        => 'ApellidoEditado',
            'fechaNacimiento'        => '1990-01-01',
            'telefono'               => 1145678901,
            'genero'                 => 'F',
            'instagram'              => '@editado',
            'telefonoMovil'          => 1145678901,
            'dni'                    => 30111222,
            'recibirMails'           => 1,
            'acepta_marketing'       => 1,
            'idPais'                 => $persona->idPais,
            'idProvincia'            => 1,
            'idLocalidad'            => 1,
            'idUnidadOrganizacional' => 0,
        ];
    }
}
