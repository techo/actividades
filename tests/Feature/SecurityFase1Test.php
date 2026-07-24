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

    /**
     * A-1: un coordinador no puede leer ni operar sobre una actividad ajena (IDOR
     * cross-actividad/cross-país). Antes estas rutas no tenían can: y solo dependían
     * de accesoBackoffice.
     *
     * @test
     */
    public function coordinador_no_puede_acceder_a_actividad_ajena()
    {
        $this->seed('PermisosSeeder');

        $coord = factory('App\Persona')->create();
        $coord->assignRole('coordinador');

        // La ajena no debe tener al coord como creador NI como último modificador
        // (la policy `editar` usa idPersonaModificacion; el factory lo setea en 1 por defecto).
        $propia = factory('App\Actividad')->create([
            'idPersonaCreacion'     => $coord->idPersona,
            'idPersonaModificacion' => $coord->idPersona,
        ]);
        $ajena  = factory('App\Actividad')->create([
            'idPersonaCreacion'     => 999999,
            'idPersonaModificacion' => 999999,
        ]);

        // Lectura de la propia: permitida.
        $this->actingAs($coord)
            ->get('/admin/ajax/actividades/'.$propia->idActividad)
            ->assertStatus(200);

        // Lectura de la ajena: denegada por la policy (can:ver).
        $this->actingAs($coord)
            ->get('/admin/ajax/actividades/'.$ajena->idActividad)
            ->assertStatus(403);

        // Escritura sobre la ajena: denegada por la policy (can:editar).
        $this->actingAs($coord)
            ->post('/admin/ajax/actividades/'.$ajena->idActividad.'/clonar')
            ->assertStatus(403);

        $this->actingAs($coord)
            ->post('/admin/ajax/actividades/'.$ajena->idActividad.'/imagen-tarjeta')
            ->assertStatus(403);
    }

    /**
     * A-1: un admin sí puede acceder a cualquier actividad (comportamiento esperado).
     *
     * @test
     */
    public function admin_puede_leer_cualquier_actividad()
    {
        $this->seed('PermisosSeeder');

        $admin = factory('App\Persona')->create();
        $admin->assignRole('admin');

        $ajena = factory('App\Actividad')->create(['idPersonaCreacion' => 999999]);

        $this->actingAs($admin)
            ->get('/admin/ajax/actividades/'.$ajena->idActividad)
            ->assertStatus(200);
    }

    /**
     * A-1: no se puede editar un punto de encuentro que pertenece a otra actividad
     * (validación hijo⊂padre en PuntosController).
     *
     * @test
     */
    public function no_se_puede_editar_punto_de_otra_actividad()
    {
        $this->seed('PermisosSeeder');

        $admin = factory('App\Persona')->create();
        $admin->assignRole('admin');

        $actividadA = factory('App\Actividad')->create();
        $actividadB = factory('App\Actividad')->create();

        // Punto que pertenece a la actividad B.
        $punto = factory('App\PuntoEncuentro')->create(['idActividad' => $actividadB->idActividad]);

        // Se intenta editarlo a través de la actividad A → 404 (no pertenece).
        $this->actingAs($admin)
            ->post('/admin/ajax/actividades/'.$actividadA->idActividad.'/puntos/'.$punto->idPuntoEncuentro, [
                'punto' => 'x', 'horario' => '10:00', 'idProvincia' => 1,
                'idLocalidad' => 1, 'idPersona' => $admin->idPersona, 'estado' => 1,
            ])
            ->assertStatus(404);
    }

    /**
     * A-2: no se puede agregar como coordinador a una persona de otro país
     * (antes la ruta usaba can:ver y no validaba el país del destino).
     *
     * @test
     */
    public function no_se_puede_agregar_coordinador_de_otro_pais()
    {
        $this->seed('PermisosSeeder');

        $pais     = factory('App\Pais')->create();
        $otroPais = factory('App\Pais')->create();

        $admin = factory('App\Persona')->create([
            'idPais'          => $pais->id,
            'idPaisPermitido' => $pais->id,
        ]);
        $admin->assignRole('admin');

        $actividad = factory('App\Actividad')->create(['idPais' => $pais->id]);

        $mismoPais = factory('App\Persona')->create(['idPais' => $pais->id]);
        $otroPaisP = factory('App\Persona')->create(['idPais' => $otroPais->id]);

        // Persona del mismo país: se agrega correctamente.
        $this->actingAs($admin)
            ->post("/admin/ajax/actividades/{$actividad->idActividad}/accesos/{$mismoPais->idPersona}")
            ->assertStatus(200);

        // Persona de otro país: rechazada.
        $this->actingAs($admin)
            ->post("/admin/ajax/actividades/{$actividad->idActividad}/accesos/{$otroPaisP->idPersona}")
            ->assertStatus(403);
    }

    /**
     * C-6: el registro NO debe confiar en el google_id del request. Sin un flujo OAuth
     * real (sesión registro_social), la cuenta no queda verificada ni se asocia el id social.
     *
     * @test
     */
    public function registro_no_confia_en_el_google_id_del_request()
    {
        $pais = factory('App\Pais')->create();

        $this->post('/ajax/usuario', [
            'nombre'     => 'Ataque',
            'apellido'   => 'Test',
            'email'      => 'nuevo-victima@ejemplo.com',
            'pais'       => $pais->id,
            'google_id'  => 'id-falso-del-atacante',
            'telefono'   => '+5491100000000',
            'privacidad' => true,
        ])->assertStatus(200);

        $persona = \App\Persona::where('mail', 'nuevo-victima@ejemplo.com')->first();
        $this->assertNotNull($persona);
        $this->assertNull($persona->email_verified_at, 'No debe quedar verificada sin OAuth real');
        $this->assertNull($persona->google_id, 'No debe asociarse el google_id del request');
    }

    /**
     * C-6: con datos verificados por OAuth en sesión, el registro social sí marca el
     * email como verificado y asocia el id social.
     *
     * @test
     */
    public function registro_social_con_sesion_verificada_queda_verificado()
    {
        $pais = factory('App\Pais')->create();

        $this->withSession(['registro_social' => [
            'email'     => 'social@ejemplo.com',
            'provider'  => 'google',
            'social_id' => 'gid-verificado',
        ]]);

        $this->post('/ajax/usuario', [
            'nombre'     => 'Social',
            'apellido'   => 'User',
            'email'      => 'social@ejemplo.com',
            'pais'       => $pais->id,
            'google_id'  => 'lo-que-mande-el-cliente-se-ignora',
            'telefono'   => '+5491100000000',
            'privacidad' => true,
        ])->assertStatus(200);

        $persona = \App\Persona::where('mail', 'social@ejemplo.com')->first();
        $this->assertNotNull($persona->email_verified_at);
        $this->assertEquals('gid-verificado', $persona->google_id);
    }
}
