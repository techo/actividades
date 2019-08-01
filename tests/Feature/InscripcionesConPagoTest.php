<?php

namespace Tests\Feature;

use App\Mail\MailInscripcionConfirmada;
use App\Mail\MailInscripcionFaltaPago;
use App\Mail\MailInscripcionPagoFueraDeFecha;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;
use \App\ActividadFactory;

class InscripcionesConPagoTest extends TestCase
{
	use RefreshDatabase;

    /** @test */
    public function usuario_puede_preinscribirse_con_pago()
    {
        $this->withoutExceptionHandling();
        Mail::fake();
        $this->seed('PermisosSeeder');

        $jose = factory('App\Persona')->create([ 'recibirMails' => 1 ]);

        $pais_con_config_de_pago = factory('App\Pais')->create([
            'config_pago' => '{
                "merchant_id": "1234",
                "account_id": "1234",
                "api_key": "7890",
                "payment_class": "PayU"
            }',
        ]);

        $actividad = app(ActividadFactory::class)
            ->conEstado('con pago')
            ->conPais($pais_con_config_de_pago->id)
            ->agregarPuntoConInscriptos(0)
            ->create();

        $datos = [
            'punto_encuentro' => $actividad->puntosEncuentro[0]->idPuntoEncuentro, 
            'aceptar_terminos' => 1 
        ];

        $this->actingAs($jose)
            ->post('/inscripciones/actividad/' . $actividad->idActividad . '/gracias', $datos)
            ->assertSessionHasNoErrors()
            ->assertSee('Sólo queda un paso');


        $this->actingAs($jose)
            ->get('/actividades/' . $actividad->idActividad)
            ->assertSee('CONFIRMAR')
            ->assertStatus(200);

        $this->assertDatabaseHas('Inscripcion', [
            'idPuntoEncuentro' => $actividad->puntosEncuentro[0]->idPuntoEncuentro,
            'idActividad' => $actividad->idActividad,
            'idPersona' => $jose->idPersona,
            'pago' => null
        ]); 

        Mail::assertQueued(MailInscripcionFaltaPago::class, 1);
    }

    /** @test */
    public function coordinador_puede_marcar_preinscripcion_como_paga()
    {
        $this->withoutExceptionHandling();

        Mail::fake();
        $this->seed('PermisosSeeder');

        $coordinador = factory('App\Persona')->create();
        $coordinador->assignRole('admin');

        $jose = factory('App\Persona')->create([ 'recibirMails' => 1 ]);

        $pais_con_config_de_pago = factory('App\Pais')->create([
            'config_pago' => '{
                "merchant_id": "1234",
                "account_id": "1234",
                "api_key": "7890",
                "payment_class": "PayU"
            }',
        ]);

        $actividad = app(ActividadFactory::class)
            ->creadaPor($coordinador)
            ->conPais($pais_con_config_de_pago->id)
            ->agregarPuntoConInscriptos(0)
            ->conEstado('con pago')
            ->create();

        $i = factory('App\Inscripcion')->create([
            'idPuntoEncuentro' => $actividad->puntosEncuentro[0]->idPuntoEncuentro,
            'idActividad' => $actividad->idActividad,
            'idPersona' => $jose->idPersona,
        ]);

        $this->actingAs($coordinador)
            ->post('/admin/ajax/actividades/' . $actividad->idActividad . '/inscripciones/' . $i->idInscripcion, [ 'pago' => 1 ])
            ->assertStatus(200);

        $this->actingAs($jose)
            ->get('/actividades/' . $actividad->idActividad)
            ->assertSee('CONFIRMADO')
            ->assertStatus(200);

        $this->assertDatabaseHas('Inscripcion', [
            'idPuntoEncuentro' => $actividad->inscripciones[0]->idPuntoEncuentro,
            'idActividad' => $actividad->idActividad,
            'idPersona' => $jose->idPersona,
            'confirma' => 0,
            'pago' => 1,
        ]); 

        Mail::assertQueued(MailInscripcionConfirmada::class, 1);
    }

    /** @test */
    public function usuario_preinscripto_puede_generar_cupon_pago()
    {
        
        $this->withoutExceptionHandling();

        $this->seed('PermisosSeeder');

        $jose = factory('App\Persona')->create([ 'recibirMails' => 1 ]);

        $pais_con_config_de_pago = factory('App\Pais')->create([
            'config_pago' => '{
                "merchant_id": "1234",
                "account_id": "1234",
                "api_key": "7890",
                "payment_class": "PayU"
            }',
        ]);

        $actividad = app(ActividadFactory::class)
            ->conPais($pais_con_config_de_pago->id)
            ->conEstado('con pago')
            ->agregarPuntoConInscriptos(0)
            ->create();

        factory('App\Inscripcion')->create([
            'idPuntoEncuentro' => $actividad->puntosEncuentro[0]->idPuntoEncuentro,
            'idActividad' => $actividad->idActividad,
            'idPersona' => $jose->idPersona
        ]);

        $datos = [ 'monto' => 100 ];

        $this->actingAs($jose)
            ->post('/inscripciones/actividad/' . $actividad->idActividad . '/confirmar/donacion/checkout', $datos)
            ->assertSessionHasNoErrors()
            ->assertOk()
            ->assertSee('Donarás $ARS 100');
    }

    /** @test */
    public function sistema_puede_marcar_pago()
    {
        $this->withoutExceptionHandling();

        Mail::fake();
        $this->seed('PermisosSeeder');

        $jose = factory('App\Persona')->create([ 'recibirMails' => 1 ]);

        $pais_con_config_de_pago = factory('App\Pais')->create([
            'config_pago' => '{
                "merchant_id": "1234",
                "account_id": "1234",
                "api_key": "7890",
                "payment_class": "PayU"
            }',
        ]);

        $actividad = app(ActividadFactory::class)
            ->conPais($pais_con_config_de_pago->id)
            ->conEstado('con pago')
            ->agregarPuntoConInscriptos(0)
            ->create([ 'fechaLimitePago' => Carbon::now()->format('Y-m-d H:i:s') ]);

        $i = factory('App\Inscripcion')->create([
            'idPuntoEncuentro' => $actividad->puntosEncuentro[0]->idPuntoEncuentro,
            'idActividad' => $actividad->idActividad,
            'idPersona' => $jose->idPersona
        ]);

        $datos = [
            'state_pol' => '4',
            'transaction_date' => Carbon::now()->subDay()->format('Y-m-d H:i:s'),
        ];

        $this->actingAs($jose)
            ->post('/pagos/' . $i->idInscripcion . '/confirmation', $datos)
            ->assertOk();

        $this->assertDatabaseHas('Inscripcion', [
            'idPuntoEncuentro' => $actividad->inscripciones[0]->idPuntoEncuentro,
            'idActividad' => $actividad->idActividad,
            'idPersona' => $jose->idPersona,
            'pago' => 1,
        ]);

        Mail::assertQueued(MailInscripcionConfirmada::class, 1);
    }

    /** @test */
    public function sistema_no_puede_marcar_pago_si_esta_fuera_de_la_fecha_limite()
    {
        $this->withoutExceptionHandling();

        Mail::fake();
        $this->seed('PermisosSeeder');

        $jose = factory('App\Persona')->create([ 'recibirMails' => 1 ]);

        $pais_con_config_de_pago = factory('App\Pais')->create([
            'config_pago' => '{
                "merchant_id": "1234",
                "account_id": "1234",
                "api_key": "7890",
                "payment_class": "PayU"
            }',
        ]);

        $actividad = app(ActividadFactory::class)
            ->conPais($pais_con_config_de_pago->id)
            ->conEstado('con pago')
            ->agregarPuntoConInscriptos(0)
            ->create([ 'fechaLimitePago' => Carbon::now() ]);

        $i = factory('App\Inscripcion')->create([
            'idPuntoEncuentro' => $actividad->puntosEncuentro[0]->idPuntoEncuentro,
            'idActividad' => $actividad->idActividad,
            'idPersona' => $jose->idPersona
        ]);

        $datos = [
            'state_pol' => '4',
            'transaction_date' => Carbon::now()->addDay(),
        ];

        $this->actingAs($jose)
            ->post('/pagos/' . $i->idInscripcion . '/confirmation', $datos)
            ->assertOk();

        $this->assertDatabaseHas('Inscripcion', [
            'idPuntoEncuentro' => $actividad->inscripciones[0]->idPuntoEncuentro,
            'idActividad' => $actividad->idActividad,
            'idPersona' => $jose->idPersona,
            'pago' => 0,
        ]);

        Mail::assertQueued(MailInscripcionPagoFueraDeFecha::class, 1);
    }
}
