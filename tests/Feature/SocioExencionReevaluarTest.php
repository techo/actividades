<?php

namespace Tests\Feature;

use App\ActividadFactory;
use App\Inscripcion;
use App\Mail\MailInscripcionConfirmada;
use App\Mail\MailInscripcionFaltaPago;
use App\Services\Salesforce\SocioExencionService;
use App\Services\Salesforce\SocioService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Mockery;
use Tests\TestCase;

/**
 * Re-evaluación de la exención por socio sobre inscripciones YA creadas.
 *
 * Reclamos oct-2026 (act. 41565): socios que no quedaron exentos al inscribirse
 * (se hicieron socios después, o Salesforce falló → fail-closed) quedaban con el
 * pago pendiente para siempre. Ahora se re-chequea en cada punto donde se pide
 * pagar, y el comando socios:reevaluar-exenciones repara lo pendiente.
 *
 * SocioService se stubea; la conexión real a Salesforce se cubre en SocioServiceTest.
 */
class SocioExencionReevaluarTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function stubSocio(bool $esSocio)
    {
        $mock = Mockery::mock(SocioService::class);
        $mock->shouldReceive('esSocio')->andReturn($esSocio);
        $this->app->instance(SocioService::class, $mock);
    }

    private function paisSocio()
    {
        $pais = factory('App\Pais')->create([
            'config_pago' => '{"merchant_id":"1234","account_id":"1234","api_key":"7890","payment_class":"PayU"}',
        ]);
        config([
            'services.salesforce.enabled'       => true,
            'services.salesforce.socio_pais_id' => $pais->id,
        ]);

        return $pais;
    }

    private function inscripcionPendiente($persona, $pais, array $actividad = [], array $inscripcion = [], $creador = null)
    {
        $factory = app(ActividadFactory::class)->conPais($pais->id)->agregarPuntoConInscriptos(0);
        if ($creador) {
            $factory = $factory->creadaPor($creador);
        }
        $actividad = $factory->create(array_merge(['pago' => 1, 'montoMin' => 100], $actividad));

        return factory('App\Inscripcion')->create(array_merge([
            'idActividad'      => $actividad->idActividad,
            'idPuntoEncuentro' => $actividad->puntosEncuentro[0]->idPuntoEncuentro,
            'idPersona'        => $persona->idPersona,
            'pago'             => 0,
            'exento_pago'      => 0,
        ], $inscripcion));
    }

    /** @test */
    public function reevaluar_marca_exenta_y_persiste_si_hoy_es_socio()
    {
        $this->stubSocio(true);
        $pais = $this->paisSocio();
        $i = $this->inscripcionPendiente(factory('App\Persona')->create(), $pais);

        $this->assertTrue(app(SocioExencionService::class)->reevaluar($i->fresh()));

        $this->assertDatabaseHas('Inscripcion', [
            'idInscripcion' => $i->idInscripcion,
            'exento_pago'   => 1,
            'exento_motivo' => 'socio_salesforce',
        ]);
    }

    /** @test */
    public function reevaluar_no_toca_inscripciones_ya_pagadas()
    {
        $this->stubSocio(true);
        $pais = $this->paisSocio();
        $i = $this->inscripcionPendiente(factory('App\Persona')->create(), $pais, [], ['pago' => 1]);

        $this->assertFalse(app(SocioExencionService::class)->reevaluar($i->fresh()));
        $this->assertDatabaseHas('Inscripcion', ['idInscripcion' => $i->idInscripcion, 'exento_pago' => 0]);
    }

    /** @test */
    public function reevaluar_no_exime_si_no_es_socio()
    {
        $this->stubSocio(false);
        $pais = $this->paisSocio();
        $i = $this->inscripcionPendiente(factory('App\Persona')->create(), $pais);

        $this->assertFalse(app(SocioExencionService::class)->reevaluar($i->fresh()));
        $this->assertDatabaseHas('Inscripcion', ['idInscripcion' => $i->idInscripcion, 'exento_pago' => 0]);
    }

    /** @test */
    public function coordinador_confirma_a_quien_se_hizo_socio_despues_manda_confirmada_no_falta_pago()
    {
        $this->withoutExceptionHandling();
        $this->stubSocio(true);
        Mail::fake();
        $this->seed('PermisosSeeder');

        $coordinador = factory('App\Persona')->create();
        $coordinador->assignRole('admin');
        $pais = $this->paisSocio();

        // No quedó exenta al inscribirse (exento_pago = 0), pero hoy es socio.
        $i = $this->inscripcionPendiente(
            factory('App\Persona')->create(['recibirMails' => 1]),
            $pais,
            ['confirmacion' => 1],
            ['confirma' => 0],
            $coordinador
        );

        $this->actingAs($coordinador)
            ->post('/admin/ajax/actividades/' . $i->idActividad . '/inscripciones/' . $i->idInscripcion, ['confirma' => 1])
            ->assertStatus(200);

        Mail::assertQueued(MailInscripcionConfirmada::class, 1);
        Mail::assertNotQueued(MailInscripcionFaltaPago::class);
        $this->assertDatabaseHas('Inscripcion', ['idInscripcion' => $i->idInscripcion, 'exento_pago' => 1, 'confirma' => 1]);
    }

    /** @test */
    public function comando_dry_run_no_escribe_y_commit_marca_exentas_solo_las_pendientes()
    {
        $this->stubSocio(true);
        $pais = $this->paisSocio();

        $futura = ['fechaInicio' => now()->addDays(10)];
        $pendiente = $this->inscripcionPendiente(factory('App\Persona')->create(), $pais, $futura);
        $pagada    = $this->inscripcionPendiente(factory('App\Persona')->create(), $pais, $futura, ['pago' => 1]);

        $this->artisan('socios:reevaluar-exenciones')->assertExitCode(0);
        $this->assertEquals(0, (int) Inscripcion::find($pendiente->idInscripcion)->exento_pago);

        $this->artisan('socios:reevaluar-exenciones', ['--commit' => true])->assertExitCode(0);
        $this->assertEquals(1, (int) Inscripcion::find($pendiente->idInscripcion)->exento_pago);
        $this->assertEquals(0, (int) Inscripcion::find($pagada->idInscripcion)->exento_pago);
    }
}
