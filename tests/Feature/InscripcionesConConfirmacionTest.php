<?php

namespace Tests\Feature;

use App\Mail\MailConfimacionInscripcion;
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
        //$this->withoutExceptionHandling();
        Mail::fake();
        $this->seed('PermisosSeeder');

        $jose = factory('App\Persona')->create();

        $actividad = app(ActividadFactory::class)
            ->agregarPuntoConInscriptos(0)
            //confirmacion = 1
            //pago = 0
            ->create();

        $datos = [
            'punto_encuentro' => $actividad->puntosEncuentro[0]->idPuntoEncuentro, 
            'aceptar_terminos' => 1 
        ];
        
        $this->actingAs($jose)
            //post a preinscribirse
            ->post('/inscripciones/actividad/' . $actividad->idActividad . '/gracias',$datos)
            //va a pantalla "esperar hasta que confirme el coordinador"
            ->assertStatus(200);

        $this->assertDatabaseHas('Inscripcion', [
            'idPuntoEncuentro' => $actividad->puntosEncuentro[0]->idPuntoEncuentro,
            'idActividad' => $actividad->idActividad,
            'idPersona' => $jose->idPersona,
            'confirma' => 0,
            'pago' => 0,
        ]); 

        //mail de "estás en espera"
        Mail::assertQueued(MailEsperaConfirmacion::class, 1);
    }

    /** @test */
    public function coordinador_puede_confirmar_preinscripcion_con_confirmacion()
    {
        //$this->withoutExceptionHandling();
        Mail::fake();
        $this->seed('PermisosSeeder');

        $coordinador = factory('App\Persona')->create();
        $coordinador->assignRole('coordinador');

        $actividad = app(ActividadFactory::class)
            ->creadaPor($coordinador)
            ->agregarPuntoConInscriptos(1)
            ->create();

        $this->actingAs($coordinador)
            ->post('/admin/ajax/actividades/'. $actividad->idActividad .'/inscripciones/cambiar/estado')
            //habilita al pago
            ->assertStatus(200);

        $this->assertDatabaseHas('Inscripcion', [
            'idPuntoEncuentro' => $actividad->puntosEncuentro[0]->idPuntoEncuentro,
            'idActividad' => $actividad->idActividad,
            'idPersona' => $actividad->inscripciones[0]->idPersona,
            'confirma' => 1,
        ]); 

        Mail::assertQueued(MailHabilitaInscripcion::class, 1);
    }

}
