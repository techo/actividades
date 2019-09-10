<?php

namespace Tests\Feature;

use App\ActividadFactory;
use App\Jobs\EnviarMailsCancelacionActividad;
use App\Mail\ActualizacionActividad;
use App\Mail\CancelacionActividad;
use App\Mail\InvitacionEvaluacion;
use App\Mail\MailRegistroUsuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MailingTest extends TestCase
{
	use RefreshDatabase;

    /** @test */
    public function administrador_elimina_actividad()
    {
        $this->withoutExceptionHandling();

        Mail::fake();

		$persona = factory('App\Persona')->create();
    	$permission = Permission::create(['name' => 'ver_backoffice']);
		$persona->givePermissionTo($permission);
		$permission = Permission::create(['name' => 'borrar_actividad']);
		$persona->givePermissionTo($permission);

		$persona_que_quiere_recibir_mail = factory('App\Persona')->create(['recibirMails' => true ]);

    	$actividad = factory('App\Actividad')->create();
    	$punto = $actividad->puntosEncuentro()->save(factory('App\PuntoEncuentro')->make());
    	$inscripcion = $punto->inscripciones()->save(factory('App\Inscripcion')->make([
    		'idActividad' => $actividad->idActividad,
    		'idPersona' => $persona_que_quiere_recibir_mail->idPersona
    	]));

        $this->actingAs($persona)
        	->delete('/admin/actividades/' . $actividad->idActividad)
        	->assertRedirect('/admin/actividades/usuario');

        $this->assertSoftDeleted('Actividad', [ 'nombreActividad' => $actividad->nombreActividad]);

        Mail::assertQueued(CancelacionActividad::class, function ($mail) use ($actividad) {
            return $mail->actividad->nombreActividad === $actividad->nombreActividad;
        });
    }

    /** @test */
    public function administrador_asigna_punto()
    {
        $this->withoutExceptionHandling();

        Mail::fake();

        $this->seed('PermisosSeeder');

        $admin = factory('App\Persona')->create();
        $admin->assignRole('admin');

        $persona_que_quiere_recibir_mail = factory('App\Persona')->create(['recibirMails' => true ]);

        $actividad = app(ActividadFactory::class)
            ->creadaPor($admin)
            ->agregarPuntoConInscriptos(0)
            ->agregarInscripto($persona_que_quiere_recibir_mail)
            ->create();

        $datos = [
            'inscripciones' => [ $actividad->inscripciones[0]->idInscripcion ],
            'punto' => $actividad->puntosEncuentro[0]->idPuntoEncuentro,
        ];

        $this->actingAs($admin)
            ->post('/admin/ajax/actividades/' . $actividad->idActividad . '/inscripciones/asignar/punto', $datos)
            ->assertStatus(200);

        $this->assertDatabaseHas('Inscripcion', [ 
            'idInscripcion' => $actividad->inscripciones[0]->idInscripcion, 
            'idPuntoEncuentro' => $actividad->puntosEncuentro[0]->idPuntoEncuentro
        ]);

        Mail::assertQueued(ActualizacionActividad::class, 1);

    }

    /** @test */
    public function administrador_envia_evaluaciones()
    {
        $this->withoutExceptionHandling();

        Mail::fake();

        $this->seed('PermisosSeeder');

        $admin = factory('App\Persona')->create();
        $admin->assignRole('admin');

        $maria = factory('App\Persona')->create(['recibirMails' => true ]);
        $esteban = factory('App\Persona')->create(['recibirMails' => true ]);

        $actividad = app(ActividadFactory::class)
            ->creadaPor($admin)
            ->agregarPuntoConInscriptos(0)
            ->agregarInscripto($maria, 'presente')
            ->agregarInscripto($esteban)
            ->create();

        $this->actingAs($admin)
            ->post('/admin/ajax/actividades/' . $actividad->idActividad . '/enviar-evaluaciones')
            ->assertStatus(200);

        Mail::assertQueued(InvitacionEvaluacion::class, 1);

    }
}
