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

        $fecha_limite = Carbon::now();

        $actividad = app(ActividadFactory::class)
            ->conPais($pais_con_config_de_pago->id)
            ->conEstado('con pago')
            ->agregarPuntoConInscriptos(0)
            ->create([ 'fechaLimitePago' => $fecha_limite ]);

        $i = factory('App\Inscripcion')->create([
            'idPuntoEncuentro' => $actividad->puntosEncuentro[0]->idPuntoEncuentro,
            'idActividad' => $actividad->idActividad,
            'idPersona' => $jose->idPersona
        ]);

        $datos = [
            'state_pol' => '4',
            'transaction_date' => $fecha_limite->subDay()->format('Y-m-d H:i:s'),
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


        $fecha_transaccion = $fecha_limite->subDay()->format('Y-m-d');

        $this->actingAs($jose)
            ->get('/pagos/' . $i->idInscripcion . '/response?lapResponseCode=APPROVED&processingDate=' . $fecha_transaccion)
            ->assertSeeText('¡Participación confirmada!')
            ->assertOk();
    }

    /** @test */
    //http://localhost:8000/pagos/1270157/response?merchantId=508029&merchant_name=Test+PayU+Test+comercio&merchant_address=Av+123+Calle+12&telephone=7512354&merchant_url=http%3A%2F%2Fpruebaslapv.xtrweb.com&transactionState=4&lapTransactionState=APPROVED&message=APPROVED&referenceCode=Capacitaciones-Voluntario-31925539-18215-1270157&reference_pol=846114838&transactionId=0a456d91-04d2-4947-8109-b8661bb14c07&description=Capacitaciones%2C+18215%2C+Escuela+de+Voluntariado+2.0+-+Sede+La+Plata%2C+Buenos+Aires%2C+17%2F08%2F2019&trazabilityCode=00000000&cus=00000000&orderLanguage=es&extra1=&extra2=&extra3=&polTransactionState=4&signature=e09cf3bee7a949a5b0bf456af8b33b4b&polResponseCode=1&lapResponseCode=APPROVED&risk=&polPaymentMethod=257&lapPaymentMethod=VISA&polPaymentMethodType=2&lapPaymentMethodType=CREDIT_CARD&installmentsNumber=1&TX_VALUE=200.00&TX_TAX=.00&currency=ARS&lng=es&pseCycle=&buyerEmail=marcos.wolff%40techo.org&pseBank=&pseReference1=&pseReference2=&pseReference3=&authorizationCode=00000000&processingDate=2019-08-02
    public function sistema_no_puede_marcar_pago_si_esta_fuera_de_la_fecha_limite()
    {
        $this->withoutExceptionHandling();

        //Mail::fake();
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

        $fecha_limite = Carbon::now();

        $actividad = app(ActividadFactory::class)
            ->conPais($pais_con_config_de_pago->id)
            ->conEstado('con pago')
            ->agregarPuntoConInscriptos(0)
            ->create([ 'fechaLimitePago' => $fecha_limite ]);

        $i = factory('App\Inscripcion')->create([
            'idPuntoEncuentro' => $actividad->puntosEncuentro[0]->idPuntoEncuentro,
            'idActividad' => $actividad->idActividad,
            'idPersona' => $jose->idPersona
        ]);

        $datos = [
            'state_pol' => '4',
            'transaction_date' => Carbon::now()->addDay()->format('Y-m-d H:i:s'),
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

        //Mail::assertQueued(MailInscripcionPagoFueraDeFecha::class, 1);

        $fecha_transaccion = $fecha_limite->addDay()->format('Y-m-d');

        $this->actingAs($jose)
            ->get('/pagos/' . $i->idInscripcion . '/response?lapResponseCode=APPROVED&processingDate=' . $fecha_transaccion)
            ->assertSeeText('¡Pago fuera de fecha!')
            ->assertOk();
    }

    /** @test */
    public function plataforma_reenvia_a_pagina_confirmada()
    {
        //$this->withoutExceptionHandling();

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

        $fecha_limite = Carbon::now();

        $actividad = app(ActividadFactory::class)
            ->conPais($pais_con_config_de_pago->id)
            ->conEstado('con pago')
            ->agregarPuntoConInscriptos(0)
            ->create([ 'fechaLimitePago' => $fecha_limite ]);

        $i = factory('App\Inscripcion')->create([
            'idPuntoEncuentro' => $actividad->puntosEncuentro[0]->idPuntoEncuentro,
            'idActividad' => $actividad->idActividad,
            'idPersona' => $jose->idPersona
        ]);

        $fecha_transaccion = $fecha_limite->subDay()->format('Y-m-d');

        $this->actingAs($jose)
            ->get('/pagos/' . $i->idInscripcion . '/response?lapResponseCode=APPROVED&processingDate=' . $fecha_transaccion)
            ->assertSeeText('¡Participación confirmada!')
            ->assertOk();

    }

    /** @test */
    public function plataforma_reenvia_a_pagina_transaccion_error()
    {
        //$this->withoutExceptionHandling();

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

        $i = factory('App\Inscripcion')->create([
            'idPuntoEncuentro' => $actividad->puntosEncuentro[0]->idPuntoEncuentro,
            'idActividad' => $actividad->idActividad,
            'idPersona' => $jose->idPersona
        ]);

        $this->actingAs($jose)
            ->get('/pagos/' . $i->idInscripcion . '/response?lapResponseCode=OTRA COSA')
            ->assertSeeText('No pudimos procesar la transacción')
            ->assertOk();

    }
}
