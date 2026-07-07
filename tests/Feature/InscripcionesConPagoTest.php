<?php

namespace Tests\Feature;

use App\Mail\MailInscripcionConfirmada;
use App\Mail\MailInscripcionEsperarConfirmacion;
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

    /**
     * Replica la regla de firma de PayU Latam para la Confirmation URL, para
     * poder armar en los tests el mismo `sign` que PayU calcularía en
     * producción. Ver App\Payments\Concerns\VerifiesPayuSignature.
     */
    private function payuConfirmationSign($apiKey, $merchantId, $referenceSale, $value, $currency, $statePol)
    {
        $parts = explode('.', $value);

        if (!isset($parts[1]) || $parts[1] === '') {
            $formatted = $parts[0] . '.0';
        } elseif (strlen($parts[1]) > 1 && $parts[1][1] !== '0') {
            $formatted = $parts[0] . '.' . substr($parts[1], 0, 2);
        } else {
            $formatted = $parts[0] . '.' . $parts[1][0];
        }

        return md5(implode('~', [$apiKey, $merchantId, $referenceSale, $formatted, $currency, $statePol]));
    }

    /**
     * Arma el mismo reference_sale que App\Payments\PayU::referenceCode()
     * generaría para una inscripción real, para poder firmarlo en los tests.
     */
    private function payuReferenceSale($actividad, $persona, $inscripcion)
    {
        return $actividad->tipo->nombre . '-'
            . 'Voluntario-'
            . $persona->dni . '-'
            . $actividad->idActividad . '-'
            . $inscripcion->idInscripcion;
    }

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
            ->assertSee('last_step_confirm_by_donation');

        $this->actingAs($jose)
            ->get('/actividades/' . $actividad->idActividad)
            ->assertSee('approval_needed')
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
    public function usuario_no_puede_confirmar_con_pago_si_paso_fecha_limite()
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
            ->conEstado('con pago')
            ->conPais($pais_con_config_de_pago->id)
            ->agregarPuntoConInscriptos(0)
            ->create([ 'fechaLimitePago' => Carbon::now()->subDay() ]);

        factory('App\Inscripcion')->create([
            'idPuntoEncuentro' => $actividad->puntosEncuentro->first()->idPuntoEncuentro,
            'idActividad' => $actividad->idActividad,
            'idPersona' => $jose->idPersona,
        ]);

        $this->actingAs($jose)
            ->get('/actividades/' . $actividad->idActividad)
            ->assertSee('confirmation_date_is_closed')
            ->assertStatus(200);
    }

    /** @test */
    public function usuario_no_puede_confirmar_con_pago_si_paso_fecha_limite_con_confirmación()
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
            ->conEstado('con confirmacion y pago')
            ->conPais($pais_con_config_de_pago->id)
            ->agregarPuntoConInscriptos(0)
            ->create([ 'fechaLimitePago' => Carbon::now()->subDay() ]);

        factory('App\Inscripcion')->create([
            'idPuntoEncuentro' => $actividad->puntosEncuentro->first()->idPuntoEncuentro,
            'idActividad' => $actividad->idActividad,
            'idPersona' => $jose->idPersona,
            'confirma' => 1,
        ]);

        $this->actingAs($jose)
            ->get('/actividades/' . $actividad->idActividad)
            ->assertSee('confirmation_date_is_closed')
            ->assertStatus(200);
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
            ->assertSee('confirmed')
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
            ->assertSee('$ARS 100');
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

        $referenceSale = $this->payuReferenceSale($actividad, $jose, $i);

        $datos = [
            'state_pol' => '4',
            'transaction_date' => $fecha_limite->subDay()->format('Y-m-d H:i:s'),
            'merchant_id' => '1234',
            'reference_sale' => $referenceSale,
            'value' => '100.00',
            'currency' => 'ARS',
            'sign' => $this->payuConfirmationSign('7890', '1234', $referenceSale, '100.00', 'ARS', '4'),
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
            ->assertSeeText('inscription_confirmed')
            ->assertOk();
    }

    /** @test */
    public function sistema_puede_marcar_pago_si_no_hay_fecha_limite()
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
            ->create();

        $i = factory('App\Inscripcion')->create([
            'idPuntoEncuentro' => $actividad->puntosEncuentro[0]->idPuntoEncuentro,
            'idActividad' => $actividad->idActividad,
            'idPersona' => $jose->idPersona
        ]);

        $fecha_transaccion = Carbon::now()->format('Y-m-d');

        $referenceSale = $this->payuReferenceSale($actividad, $jose, $i);

        $datos = [
            'state_pol' => '4',
            'transaction_date' => $fecha_transaccion,
            'merchant_id' => '1234',
            'reference_sale' => $referenceSale,
            'value' => '100.00',
            'currency' => 'ARS',
            'sign' => $this->payuConfirmationSign('7890', '1234', $referenceSale, '100.00', 'ARS', '4'),
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

        $this->actingAs($jose)
            ->get('/pagos/' . $i->idInscripcion . '/response?lapResponseCode=APPROVED&processingDate=' . $fecha_transaccion)
            ->assertSeeText('inscription_confirmed')
            ->assertOk();
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

        $referenceSale = $this->payuReferenceSale($actividad, $jose, $i);

        $datos = [
            'state_pol' => '4',
            'transaction_date' => Carbon::now()->addDay()->format('Y-m-d H:i:s'),
            'merchant_id' => '1234',
            'reference_sale' => $referenceSale,
            'value' => '100.00',
            'currency' => 'ARS',
            'sign' => $this->payuConfirmationSign('7890', '1234', $referenceSale, '100.00', 'ARS', '4'),
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

        $fecha_transaccion = $fecha_limite->addDay()->format('Y-m-d');

        $this->actingAs($jose)
            ->get('/pagos/' . $i->idInscripcion . '/response?lapResponseCode=APPROVED&processingDate=' . $fecha_transaccion)
            ->assertSeeText('¡Pago fuera de fecha!')
            ->assertOk();
    }

    /** @test */
    public function sistema_rechaza_confirmacion_de_pago_con_firma_invalida()
    {
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
            ->create();

        $i = factory('App\Inscripcion')->create([
            'idPuntoEncuentro' => $actividad->puntosEncuentro[0]->idPuntoEncuentro,
            'idActividad' => $actividad->idActividad,
            'idPersona' => $jose->idPersona,
        ]);

        $referenceSale = $this->payuReferenceSale($actividad, $jose, $i);

        // Payload con todos los datos correctos salvo la firma, que es la que
        // forjaría un atacante que no conoce el api_key del país.
        $datos = [
            'state_pol' => '4',
            'transaction_date' => Carbon::now()->format('Y-m-d H:i:s'),
            'merchant_id' => '1234',
            'reference_sale' => $referenceSale,
            'value' => '100.00',
            'currency' => 'ARS',
            'sign' => 'firma-forjada-por-un-atacante',
        ];

        $this->actingAs($jose)
            ->post('/pagos/' . $i->idInscripcion . '/confirmation', $datos)
            ->assertStatus(403);

        $this->assertDatabaseMissing('Inscripcion', [
            'idInscripcion' => $i->idInscripcion,
            'pago' => 1,
        ]);

        Mail::assertNothingQueued();
    }

    /** @test */
    public function sistema_rechaza_confirmacion_de_pago_con_reference_sale_de_otra_inscripcion()
    {
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
            ->create();

        $i = factory('App\Inscripcion')->create([
            'idPuntoEncuentro' => $actividad->puntosEncuentro[0]->idPuntoEncuentro,
            'idActividad' => $actividad->idActividad,
            'idPersona' => $jose->idPersona,
        ]);

        // Firma válida y matemáticamente correcta, pero calculada para un
        // reference_sale de OTRA transacción (por ejemplo, una compra real y
        // legítima de $1 que hizo el propio atacante). Reenviarla contra el
        // idInscripcion de esta URL debe rechazarse igual, aunque la firma en
        // sí sea válida para esos otros datos.
        $referenceSaleDeOtraTransaccion = 'Otro-Tipo-Voluntario-99999999-1-1';

        $datos = [
            'state_pol' => '4',
            'transaction_date' => Carbon::now()->format('Y-m-d H:i:s'),
            'merchant_id' => '1234',
            'reference_sale' => $referenceSaleDeOtraTransaccion,
            'value' => '100.00',
            'currency' => 'ARS',
            'sign' => $this->payuConfirmationSign('7890', '1234', $referenceSaleDeOtraTransaccion, '100.00', 'ARS', '4'),
        ];

        $this->actingAs($jose)
            ->post('/pagos/' . $i->idInscripcion . '/confirmation', $datos)
            ->assertStatus(403);

        $this->assertDatabaseMissing('Inscripcion', [
            'idInscripcion' => $i->idInscripcion,
            'pago' => 1,
        ]);

        Mail::assertNothingQueued();
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

        $fecha_limite = Carbon::parse(Carbon::now()->format('Y-m-d'));

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
            ->assertSeeText('inscription_confirmed')
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

    /** @test */
    public function plataforma_reenvia_a_pagina_fecha_limite_vencida()
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

        $fecha_limite = Carbon::parse(Carbon::now()->format('Y-m-d'));

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

        $fecha_transaccion = $fecha_limite->format('Y-m-d');

        $this->actingAs($jose)
            ->get('/pagos/' . $i->idInscripcion . '/response?lapResponseCode=APPROVED&processingDate=' . $fecha_transaccion)
            ->assertSeeText('¡Pago fuera de fecha!')
            ->assertOk();

    }

    /** @test */
    public function coordinador_puede_preinscribir_con_pago()
    {
        $this->withoutExceptionHandling();
        Mail::fake();
        $this->seed('PermisosSeeder');

        $coordinador = factory('App\Persona')->create();
        $coordinador->assignRole('admin');

        $actividad = app(ActividadFactory::class)
            ->creadaPor($coordinador)
            ->conEstado('con pago')
            ->agregarPuntoConInscriptos(0)
            ->conGrupoRaiz()
            ->create();

        $jose = factory('App\Persona')->create([ 'recibirMails' => 1 ]);

        $datos = [
            'idPuntoEncuentro' => $actividad->puntosEncuentro[0]->idPuntoEncuentro, 
            'idPersona' => $jose->idPersona,
            'notificar' => 1,
        ];

        $this->actingAs($coordinador)
            ->post('/admin/ajax/actividades/' . $actividad->idActividad . '/inscripciones/', $datos)
            ->assertSessionHasNoErrors()
            ->assertStatus(200);

        Mail::assertQueued(MailInscripcionFaltaPago::class, 1);
    }

    /** @test */
    public function coordinador_puede_preinscribir_con_confirmacion_y_pago()
    {
        $this->withoutExceptionHandling();
        Mail::fake();
        $this->seed('PermisosSeeder');

        $coordinador = factory('App\Persona')->create();
        $coordinador->assignRole('admin');

        $actividad = app(ActividadFactory::class)
            ->creadaPor($coordinador)
            ->conEstado('con confirmacion y pago')
            ->agregarPuntoConInscriptos(0)
            ->conGrupoRaiz()
            ->create();

        $jose = factory('App\Persona')->create([ 'recibirMails' => 1 ]);

        $datos = [
            'idPuntoEncuentro' => $actividad->puntosEncuentro[0]->idPuntoEncuentro, 
            'idPersona' => $jose->idPersona,
            'notificar' => 1,
        ];

        $i = factory('App\Inscripcion')->create([
            'idPuntoEncuentro' => $actividad->puntosEncuentro[0]->idPuntoEncuentro,
            'idActividad' => $actividad->idActividad,
            'idPersona' => $jose->idPersona
        ]);
        $i->delete();

        $this->actingAs($coordinador)
            ->post('/admin/ajax/actividades/' . $actividad->idActividad . '/inscripciones/', $datos)
            ->assertSessionHasNoErrors()
            ->assertStatus(200);

        Mail::assertQueued(MailInscripcionEsperarConfirmacion::class, 1);
    }

}
