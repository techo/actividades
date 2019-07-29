<?php

namespace Tests\Feature;

use App\Mail\MailConfimacionInscripcion;
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
            ->conPais($pais_con_config_de_pago->id)
            ->deTipo(factory('App\Tipo')->create([ 'flujo' => 'CONSTRUCCION']))
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

        $this->assertDatabaseHas('Inscripcion', [
            'idPuntoEncuentro' => $actividad->puntosEncuentro[0]->idPuntoEncuentro,
            'idActividad' => $actividad->idActividad,
            'idPersona' => $jose->idPersona,
            'estado' => "Pre-Inscripto"
        ]); 

        Mail::assertQueued(MailConfimacionInscripcion::class, 1);
    }

    /** @test */
    public function usuario_preinscripto_puede_hacer_pago()
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
            ->deTipo(factory('App\Tipo')->create([ 'flujo' => 'CONSTRUCCION']))
            ->agregarPuntoConInscriptos(0)
            ->create();

        factory('App\Inscripcion')->state('preinscripto')->create([
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
    public function coordinador_puede_confirmar_preinscripcion_con_pago()
    {
        Mail::fake();
        $this->seed('PermisosSeeder');

        $coordinador = factory('App\Persona')->create();
        $coordinador->assignRole('coordinador');

        $actividad = app(ActividadFactory::class)
            ->creadaPor($coordinador)
            ->agregarPuntoConInscriptos(1)
            ->create();

        //finaliza la inscripción
        $this->actingAs($coordinador)
            ->post('/inscripciones/actividad/' . $actividad->idActividad . '/gracias')
            //post a preinscribirse
            //va a pantalla "solo falta un paso"
            ->assertStatus(200);

        $this->assertDatabaseHas('Inscripcion', [
            'idPuntoEncuentro' => $actividad->inscripciones[0]->idPuntoEncuentro,
            'idActividad' => $actividad->idActividad,
            'idPersona' => $actividad->inscripciones[0]->idPersona,
            'confirma' => 1,
        ]); 

        Mail::assertQueued(MailFinalizaInscripcion::class, 1);
    }

    public function coordinador_puede_marcar_pago()
    {
        //$this->withoutExceptionHandling();
        //finaliza la inscripción
        //mail de "recibimos tu pago"
    }

    public function sistema_puede_marcar_pago()
    {
        //$this->withoutExceptionHandling();
        //finaliza la inscripción
        //mail de "recibimos tu pago"
    }

        /** @test */
    
    public function usuario_puede_preinscribirse_con_confirmacion_y_pago()
    {
        //$this->withoutExceptionHandling();
        Mail::fake();
        $this->seed('PermisosSeeder');

        $jose = factory('App\Persona')->create();

        $actividad = app(ActividadFactory::class)
            ->agregarPuntoConInscriptos(1)
            //confirmacion = 1
            //pago = 1
            ->create();

        $datos = [
            'punto_encuentro' => $actividad->puntosEncuentro[0]->idPuntoEncuentro, 
            'aceptar_terminos' => 1 
        ];

        $this->actingAs($jose)
            ->post('/inscripciones/actividad/' . $actividad->idActividad . '/gracias',$datos)
            //post a preinscribirse
            //va a pantalla "solo falta un paso"
            ->assertStatus(200);

        $this->assertDatabaseHas('Inscripcion', [
            'idPuntoEncuentro' => $actividad->inscripciones[0]->idPuntoEncuentro,
            'idActividad' => $actividad->idActividad,
            'idPersona' => $jose->idPersona,
            'confirma' => 0,
            'pago' => 0,
        ]); 

        //mail de "confirmá con tu donación"
        Mail::assertQueued(MailPagoInscripcion::class, 1);
    }

}
