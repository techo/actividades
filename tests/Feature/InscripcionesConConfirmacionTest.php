<?php

namespace Tests\Feature;

use App\Mail\MailInscripcionConfirmada;
use App\Mail\MailInscripcionEsperarConfirmacion;
use App\Mail\MailInscripcionFaltaPago;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;
use \App\ActividadFactory;

class InscripcionesConConfirmacionTest extends TestCase
{
	use RefreshDatabase;

    /** @test */
    public function usuario_puede_preinscribirse_con_confirmacion()
    {    
        $this->withoutExceptionHandling();
        Mail::fake();
        $this->seed('PermisosSeeder');

        $jose = factory('App\Persona')->create([ 'recibirMails' => 1 ]);

        $actividad = app(ActividadFactory::class)
            ->conEstado('con confirmacion')
            ->agregarPuntoConInscriptos(0)
            ->create();

        $datos = [
            'punto_encuentro' => $actividad->puntosEncuentro[0]->idPuntoEncuentro, 
            'aceptar_terminos' => 1 
        ];
        
        $this->actingAs($jose)
            ->post('/inscripciones/actividad/' . $actividad->idActividad . '/gracias',$datos)
            ->assertSee('espera')
            ->assertStatus(200);

        $this->actingAs($jose)
            ->get('/actividades/' . $actividad->idActividad)
            ->assertSee('ESPERAR')
            ->assertStatus(200);

        $this->assertDatabaseHas('Inscripcion', [
            'idPuntoEncuentro' => $actividad->puntosEncuentro[0]->idPuntoEncuentro,
            'idActividad' => $actividad->idActividad,
            'idPersona' => $jose->idPersona,
            'confirma' => 0,
        ]); 

        //mail de "estás en espera"
        Mail::assertQueued(MailInscripcionEsperarConfirmacion::class, 1);
    }

    /** @test */
    public function coordinador_puede_confirmar_preinscripcion_con_confirmacion()
    {
        $this->withoutExceptionHandling();
        Mail::fake();
        $this->seed('PermisosSeeder');

        $coordinador = factory('App\Persona')->create();
        $coordinador->assignRole('admin');

        $actividad = app(ActividadFactory::class)
            ->creadaPor($coordinador)
            ->conEstado('con confirmacion')
            ->agregarPuntoConInscriptos(0)
            ->create();

        $jose = factory('App\Persona')->create([ 'recibirMails' => 1 ]);

        $i = factory('App\Inscripcion')->create([
            'idPuntoEncuentro' => $actividad->puntosEncuentro[0]->idPuntoEncuentro,
            'idActividad' => $actividad->idActividad,
            'idPersona' => $jose->idPersona,
        ]);

        $this->actingAs($coordinador)
            ->post('/admin/ajax/actividades/' . $actividad->idActividad . '/inscripciones/' . $i->idInscripcion, [ 'confirma' => 1 ])
            ->assertStatus(200);

        $this->actingAs($jose)
            ->get('/actividades/' . $actividad->idActividad)
            ->assertSee('CONFIRMADO')
            ->assertStatus(200);

        $this->assertDatabaseHas('Inscripcion', [
            'idPuntoEncuentro' => $actividad->puntosEncuentro[0]->idPuntoEncuentro,
            'idActividad' => $actividad->idActividad,
            'idPersona' => $actividad->inscripciones[0]->idPersona,
            'confirma' => 1,
        ]); 

        Mail::assertQueued(MailInscripcionConfirmada::class, 1);
    }

    /** @test */
    public function usuario_puede_preinscribirse_con_confirmacion_y_pago()
    {    
        $this->withoutExceptionHandling();

        Mail::fake();
        $this->seed('PermisosSeeder');

        $jose = factory('App\Persona')->create([ 'recibirMails' => 1 ]);

        $actividad = app(ActividadFactory::class)
            ->conEstado('con confirmacion y pago')
            ->agregarPuntoConInscriptos(0)
            ->create();

        $datos = [
            'punto_encuentro' => $actividad->puntosEncuentro[0]->idPuntoEncuentro, 
            'aceptar_terminos' => 1 
        ];
        
        $this->actingAs($jose)
            ->post('/inscripciones/actividad/' . $actividad->idActividad . '/gracias', $datos)
            ->assertSee('espera')
            ->assertStatus(200);

        $this->actingAs($jose)
            ->get('/actividades/' . $actividad->idActividad)
            ->assertSee('ESPERAR')
            ->assertStatus(200);

        $this->assertDatabaseHas('Inscripcion', [
            'idPuntoEncuentro' => $actividad->puntosEncuentro[0]->idPuntoEncuentro,
            'idActividad' => $actividad->idActividad,
            'idPersona' => $jose->idPersona,
            'confirma' => 0,
            'pago' => null,
        ]); 

        //mail de "estás en espera"
        Mail::assertQueued(MailInscripcionEsperarConfirmacion::class, 1);
    }

    /** @test */
    public function coordinador_puede_confirmar_preinscripcion_con_confirmacion_y_pago()
    {
        $this->withoutExceptionHandling();
        Mail::fake();
        $this->seed('PermisosSeeder');

        $coordinador = factory('App\Persona')->create();
        $coordinador->assignRole('admin');

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
            ->conEstado('con confirmacion y pago')
            ->conPais($pais_con_config_de_pago->id)
            ->agregarPuntoConInscriptos(0)
            ->create();

        $jose = factory('App\Persona')->create([ 'recibirMails' => 1 ]);

        $i = factory('App\Inscripcion')->create([
            'idPuntoEncuentro' => $actividad->puntosEncuentro[0]->idPuntoEncuentro,
            'idActividad' => $actividad->idActividad,
            'idPersona' => $jose->idPersona,
        ]);

        $this->actingAs($coordinador)
            ->post('/admin/ajax/actividades/' . $actividad->idActividad . '/inscripciones/' . $i->idInscripcion, [ 'confirma' => 1 ])
            ->assertStatus(200);

        $this->actingAs($jose)
            ->get('/actividades/' . $actividad->idActividad)
            ->assertSee('CONFIRMÁ')
            ->assertStatus(200);

        $this->assertDatabaseHas('Inscripcion', [
            'idPuntoEncuentro' => $actividad->puntosEncuentro[0]->idPuntoEncuentro,
            'idActividad' => $actividad->idActividad,
            'idPersona' => $actividad->inscripciones[0]->idPersona,
            'confirma' => 1,
        ]); 

        Mail::assertQueued(MailInscripcionFaltaPago::class, 1);
    }

}
