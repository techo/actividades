<?php

namespace Tests\Feature;

use App\Notifications\RegistroUsuario;
use App\Notifications\VerifyEmail;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;
use \App\ActividadFactory;

class UsuariosTest extends TestCase
{
	use RefreshDatabase;

	/** @test */
	public function puede_eliminar_su_cuenta()
	{
		$this->withoutExceptionHandling();

		$maria = factory('App\Persona')->create();

        $actividad = app(ActividadFactory::class)
        	->conEstado('futura')
            ->agregarInscripto($maria)
            ->create();

        $otra_actividad = app(ActividadFactory::class)
        	->conEstado('pasada')
            ->agregarInscripto($maria)
            ->create();

        $otra_actividad_mas = app(ActividadFactory::class)
            ->conEstado('futura')
            ->agregarPuntoConInscriptos(4)
            ->create();

        $response = $this->actingAs($maria)
            ->delete('/ajax/usuario')
            ->assertStatus(302);
            
        $this->assertDatabaseHas('Persona',[
        	'idPersona' => $maria->idPersona,
        	'nombres' => 'Usuario eliminado',
        ]);

        $this->assertTrue($maria->inscripciones()->count() == 1);

        //no borra otras inscripciones futuras ;)
        $this->assertTrue($otra_actividad_mas->inscripciones()->count() == 4);
		
	}

    /** @test */
    public function puede_ver_solo_actividades_que_esta_inscripto()
    {
        //$this->withoutExceptionHandling();
        
        $maria = factory('App\Persona')->create();

        $actividad = app(ActividadFactory::class)
            ->agregarInscripto($maria)
            ->create();

        $actividad_de_donde_me_desinscribo = app(ActividadFactory::class)
            ->agregarInscripto($maria)
            ->create();

        $actividad_de_donde_me_desinscribo->inscripciones->first()->delete();

        $response = $this->actingAs($maria)
            ->get('/ajax/usuario/inscripciones?date=')
            ->assertStatus(200);
            
        $this->assertTrue(count(json_decode($response->getContent())->data) == 1);
    }

    /** @test */
    public function puede_cambiar_su_email()
    {
        $this->withoutExceptionHandling();

        $this->seed('PermisosSeeder');

        Notification::fake();
        
        $maria = factory('App\Persona')->create([ 'email_verified_at' => Carbon::now() ]);

        $response = $this->actingAs($maria)
            ->post('/perfil/actualizar_email', [ 'email' => 'nuevo@email.com'])
            ->assertRedirect('/');

        $this->assertDatabaseHas('Persona', ['mail' => 'nuevo@email.com' ]);

        $this->assertTrue($maria->email_verified_at == null);
        $this->assertTrue($maria->facebook_id == null);
        $this->assertTrue($maria->google_id == null);

        Notification::assertSentTo([$maria], VerifyEmail::class);
    }

    /** @test */
    public function tiene_que_verificar_su_email_al_crear_cuenta()
    {
        $this->withoutExceptionHandling();

        $this->seed('PermisosSeeder');

        Notification::fake();

        $pais = factory('App\Pais')->create();

        $datos = [
            'apellido' => "Neso",
            'dni' => "33333333",
            'email' => "prueba@prueba.org",
            'facebook_id' => "",
            'google_id' => "",
            'localidad' => "",
            'nacimiento' => Carbon::now()->subYear(20),
            'nombre' => "Camilo",
            'pais' => $pais->id,
            'pass' => "asfsdfsdfsdf",
            'privacidad' => "true",
            'provincia' => "",
            'sexo' => "F",
            'telefono' => "432342344",
            'user' => "",
            'acepta_marketing' => "true"
        ];

        $response = $this->post('/ajax/usuario', $datos)
            ->assertOk();

        $this->assertDatabaseHas('Persona', ['mail' => 'prueba@prueba.org' ]);

        $camilo = \App\Persona::where('mail', '=', 'prueba@prueba.org')->first();

        $this->assertTrue($camilo->email_verified_at == null);

        Notification::assertSentTo([$camilo], RegistroUsuario::class);
        Notification::assertNotSentTo([$camilo], VerifyEmail::class);
    }

    /** @test */
    public function puede_hacer_login()
    {
        $this->withoutExceptionHandling();

        $this->seed('PermisosSeeder');

        $maria = factory('App\Persona')->create([ 
                'email_verified_at' => null,
                'password' => Hash::make('prueba'),
            ]);

        $datos = [
            'mail' => $maria->mail,
            'password' => 'prueba',
        ];

        $this->post('/login', $datos)
            ->assertOk();

        $this->assertAuthenticated();

    }

    /** @test */
    public function no_puede_interactuar_sin_verificar()
    {
        //$this->withoutExceptionHandling();
    
        $this->seed('PermisosSeeder');

        $maria = factory('App\Persona')->create(['email_verified_at' => null, ]);

        $this->actingAs($maria)->get('/perfil')->assertRedirect('/email/verify');
        $this->actingAs($maria)->get('/admin/actividades/usuario')->assertRedirect('/email/verify');
    }
}
