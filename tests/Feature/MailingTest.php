<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Mail;
use App\Jobs\EnviarMailsCancelacionActividad;
use App\Mail\CancelacionActividad;

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

        Mail::assertSent(CancelacionActividad::class, function ($mail) use ($actividad) {
            return $mail->actividad->nombreActividad === $actividad->nombreActividad;
        });
    }
}
