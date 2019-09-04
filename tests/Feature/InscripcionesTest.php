<?php

namespace Tests\Feature;

use App\Mail\MailInscripcionConfirmada;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;
use \App\ActividadFactory;

class InscripcionesTest extends TestCase
{
	use RefreshDatabase;

    /** @test */
    public function usuario_se_puede_inscribir()
    {
        $this->withoutExceptionHandling();

        Mail::fake();

        $persona = factory('App\Persona')->create(['recibirMails' => 1 ]);
        $persona->givePermissionTo(Permission::create(['name' => 'ver_backoffice']));

        $actividad = factory('App\Actividad')->create();
        $p = $actividad->puntosEncuentro()->save(factory('App\PuntoEncuentro')->make());

        $datos = [
            'punto_encuentro' => $p->idPuntoEncuentro, 
            'aceptar_terminos' => 1 
        ];
        
        $this->actingAs($persona)
            ->post('/inscripciones/actividad/' . $actividad->idActividad . '/gracias',$datos)
            ->assertStatus(200);

        $this->assertDatabaseHas('Inscripcion', [
            'idPuntoEncuentro' => $p->idPuntoEncuentro,
            'idActividad' => $actividad->idActividad,
            'idPersona' => $persona->idPersona
        ]);	

        $this->assertDatabaseHas('Grupo_Persona', [
            'idActividad' => $actividad->idActividad,
            'idPersona' => $persona->idPersona
        ]);

        Mail::assertQueued(MailInscripcionConfirmada::class, 1);
    }

    /** @test */
    public function usuario_no_se_puede_inscribir_si_no_hay_cupos()
    {
        //$this->withoutExceptionHandling();
        $this->seed('PermisosSeeder');

        $jose = factory('App\Persona')->create();

        $actividad = app(ActividadFactory::class)
            ->agregarPuntoConInscriptos(1)
            ->create([
                'limiteInscripciones' => 1
            ]);

        $this->get('/actividades/' . $actividad->idActividad)
            ->assertSee('cupos')
            ->assertStatus(200);

        $datos = [
            'punto_encuentro' => $actividad->puntosEncuentro->first()->idPuntoEncuentro, 
            'aceptar_terminos' => 1 
        ];
        
        $this->actingAs($jose)
            ->post('/inscripciones/actividad/' . $actividad->idActividad . '/gracias',$datos)
            ->assertStatus(403);

        $this->assertDatabaseMissing('Inscripcion', [
            'idPuntoEncuentro' => $actividad->puntosEncuentro->first()->idPuntoEncuentro,
            'idActividad' => $actividad->idActividad,
            'idPersona' => $jose->idPersona
        ]);

    }

    /** @test */
    public function usuario_no_se_puede_inscribir_si_no_esta_en_periodo_de_inscripcion()
    {
        //$this->withoutExceptionHandling();
        $this->seed('PermisosSeeder');

        $jose = factory('App\Persona')->create();

        $actividad = app(ActividadFactory::class)
            ->conEstado('pasada')
            ->agregarPuntoConInscriptos(1)
            ->create();

        $datos = [
            'punto_encuentro' => $actividad->puntosEncuentro->first()->idPuntoEncuentro, 
            'aceptar_terminos' => 1 
        ];
        
        $this->post('/inscripciones/actividad/' . $actividad->idActividad . '/gracias',$datos)
            ->assertRedirect('/email/verify');

        $this->actingAs($jose)
            ->get('/actividades/' . $actividad->idActividad)
            ->assertSee('período')
            ->assertStatus(200);

        $this->assertDatabaseMissing('Inscripcion', [
            'idPuntoEncuentro' => $actividad->puntosEncuentro->first()->idPuntoEncuentro,
            'idActividad' => $actividad->idActividad,
            'idPersona' => $jose->idPersona
        ]);

    }

    /** @test */
    public function usuario_se_puede_desinscribir()
    {
        $this->withoutExceptionHandling(); 

        $i = factory('App\Inscripcion')->create();

        $persona = $i->persona->givePermissionTo(Permission::create(['name' => 'ver_backoffice']));

        $this->actingAs($persona)
            ->delete('/ajax/usuario/inscripciones/' . $i->actividad->idActividad)
            ->assertStatus(200);

        $this->assertSoftDeleted('Inscripcion', [
            'idPuntoEncuentro' => $i->idPuntoEncuentro,
            'idActividad' => $i->idActividad,
            'idPersona' => $i->idPersona,
        ]);

        $this->assertDatabaseMissing('Grupo_Persona', [
            'idActividad' => $i->idActividad,
            'idPersona' => $i->idPersona
        ]);
    }

    /** @test */
    public function administrador_puede_inscribir_usuario()
    {
        $this->withoutExceptionHandling(); 

        Mail::fake();

        $this->seed('PermisosSeeder');

        $admin = factory('App\Persona')->create();
        $admin->assignRole('admin');

        $actividad = app(ActividadFactory::class)
            ->creadaPor($admin)
            ->agregarPuntoConInscriptos(0)
            ->conGrupoRaiz()
            ->create();

        $maria = factory('App\Persona')->create([
            'recibirMails' => 1
        ]);

        $datos = [
            'idPersona' => $maria->idPersona,
            'idPuntoEncuentro' => $actividad->puntosEncuentro->first()->idPuntoEncuentro,
            'notificar' => 0,
            //'idGrupo' => 0,
            //'rol' => '',
        ];

        $this->actingAs($admin)
            ->post('/admin/ajax/actividades/'. $actividad->idActividad .'/inscripciones', $datos)
            ->assertStatus(200);

        $this->assertDatabaseHas('Inscripcion', [
            'idPuntoEncuentro' => $actividad->inscripciones->first()->idPuntoEncuentro,
            'idActividad' => $actividad->inscripciones->first()->idActividad,
            'idPersona' => $actividad->inscripciones->first()->idPersona
        ]);

        $this->assertDatabaseHas('Grupo_Persona', [
            'idActividad' => $actividad->inscripciones->first()->idActividad,
            'idPersona' => $actividad->inscripciones->first()->idPersona,
        ]);

        Mail::assertQueued(MailConfimacionInscripcion::class, 0);
    }

    /** @test */
    public function administrador_puede_re_inscribir_usuario()
    {
        //$this->withoutExceptionHandling();

        Mail::fake();

        $this->seed('PermisosSeeder');

        $admin = factory('App\Persona')->create();
        $admin->assignRole('admin');

        $actividad = app(ActividadFactory::class)
            ->creadaPor($admin)
            ->agregarPuntoConInscriptos(0)
            ->conGrupoRaiz()
            ->create();

        $maria = factory('App\Persona')->create([
            'recibirMails' => 1
        ]);

        $datos = [
            'idPersona' => $maria->idPersona,
            'idPuntoEncuentro' => $actividad->puntosEncuentro->first()->idPuntoEncuentro,
            'notificar' => 0,
        ];

        factory('App\Inscripcion')->create([
            'idActividad' => $actividad->idActividad,
            'idPersona' => $maria->idPersona,
            'idPuntoEncuentro' => $actividad->puntosEncuentro->first()->idPuntoEncuentro,
        ])->delete();

        $this->actingAs($admin)
            ->post('/admin/ajax/actividades/'. $actividad->idActividad .'/inscripciones', $datos)
            ->assertStatus(200);

        $this->assertDatabaseHas('Inscripcion', [
            'idPuntoEncuentro' => $actividad->inscripciones->first()->idPuntoEncuentro,
            'idActividad' => $actividad->inscripciones->first()->idActividad,
            'idPersona' => $actividad->inscripciones->first()->idPersona
        ]);

        $this->assertTrue($actividad->membresias()->count() == 1);

        Mail::assertQueued(MailConfimacionInscripcion::class, 0);

    }

    /** @test */
    public function administrador_puede_desinscribir_usuario()
    {
        $this->withoutExceptionHandling(); 

        $this->seed('PermisosSeeder');

        $admin = factory('App\Persona')->create();
        $admin->assignRole('admin');

        $actividad = app(ActividadFactory::class)
            ->creadaPor($admin)
            ->agregarPuntoConInscriptos(1)
            ->create();

        $datos = [
            'idPersona' => $actividad->inscripciones->first()->idPersona,
            'idPuntoEncuentro' => $actividad->puntosEncuentro->first()->idPuntoEncuentro,
        ];

        $this->actingAs($admin)
            ->post('/admin/ajax/actividades/'. $actividad->idActividad 
                .'/inscripciones/'. $actividad->inscripciones->first()->idInscripcion 
                .'/eliminar', $datos)
            ->assertStatus(200);

        $this->assertSoftDeleted('Inscripcion', [
            'idPuntoEncuentro' => $actividad->inscripciones->first()->idPuntoEncuentro,
            'idActividad' => $actividad->inscripciones->first()->idActividad,
            'idPersona' => $actividad->inscripciones->first()->idPersona,
        ]);

        $this->assertDatabaseMissing('Grupo_Persona', [
            'idActividad' => $actividad->inscripciones->first()->idActividad,
            'idPersona' => $actividad->inscripciones->first()->idPersona
        ]);
    }

    /** @test */
    public function administrador_puede_desinscribir_usuarios_masivamente()
    {
        $this->withoutExceptionHandling(); 

        $this->seed('PermisosSeeder');

        $admin = factory('App\Persona')->create();
        $admin->assignRole('admin');

        $actividad = app(ActividadFactory::class)
            ->creadaPor($admin)
            ->agregarPuntoConInscriptos(5)
            ->create();

        $datos = [
            'inscripciones' => $actividad->inscripciones->pluck('idInscripcion')->toArray()
        ];

        $this->actingAs($admin)
            ->post('/admin/ajax/actividades/'. $actividad->idActividad .'/inscripciones/desinscribir', $datos)
            ->assertStatus(200);

        $this->assertTrue($actividad->inscripciones()->withTrashed()->count() == 5);

        $this->assertTrue($actividad->membresias()->count() == 0);

    }

    /** @test */
    public function administrador_puede_ver_iscriptos()
    {
        $this->withoutExceptionHandling(); 

        $this->seed('PermisosSeeder');

        $admin = factory('App\Persona')->create();
        $admin->assignRole('admin');

        $actividad = app(ActividadFactory::class)
            ->creadaPor($admin)
            ->agregarPuntoConInscriptos(5)
            ->create();

        $datos = [
            'idPersona' => $actividad->inscripciones->first()->idPersona,
            'idPuntoEncuentro' => $actividad->puntosEncuentro->first()->idPuntoEncuentro,
        ];

        $response = $this->actingAs($admin)
            ->get('/admin/ajax/actividades/'. $actividad->idActividad 
                .'/inscripciones')
            ->assertStatus(200);

        $this->assertTrue(count(json_decode($response->getContent())->data) == 5);
    }

    /** @test */
    public function administrador_no_puede_ver_inscriptos_eliminados()
    {
        $this->withoutExceptionHandling(); 

        $this->seed('PermisosSeeder');

        $admin = factory('App\Persona')->create();
        $admin->assignRole('admin');

        $actividad = app(ActividadFactory::class)
            ->creadaPor($admin)
            ->agregarPuntoConInscriptos(5)
            ->create();

        $datos = [
            'idPersona' => $actividad->inscripciones->first()->idPersona,
            'idPuntoEncuentro' => $actividad->puntosEncuentro->first()->idPuntoEncuentro,
        ];

        $actividad->inscripciones->first()->delete();   

        $response = $this->actingAs($admin)
            ->get('/admin/ajax/actividades/'. $actividad->idActividad .'/inscripciones')
            ->assertStatus(200);

        $this->assertTrue(count(json_decode($response->getContent())->data) == 4);
    }

    /** @test */
    public function solo_administrador_puede_ver_inscripciones_de_usuario()
    {
        //$this->withoutExceptionHandling(); 

        $this->seed('PermisosSeeder');

        $admin = factory('App\Persona')->create();
        $admin->assignRole('admin');

        $coordinador = factory('App\Persona')->create();
        $coordinador->assignRole('coordinador');

        $nestor = factory('App\Persona')->create();

        app(ActividadFactory::class)->agregarInscripto($nestor)->create();
        app(ActividadFactory::class)->agregarInscripto($nestor)->create();
        app(ActividadFactory::class)->agregarInscripto($nestor)->create();

        $response = $this->actingAs($admin)
            ->get('/admin/ajax/usuarios/'. $nestor->idPersona .'/inscripciones')
            ->assertStatus(200);

        $this->assertTrue(count(json_decode($response->getContent())->data) == 3);

        $response = $this->actingAs($coordinador)
            ->get('/admin/ajax/usuarios/'. $nestor->idPersona .'/inscripciones')
            ->assertStatus(403);
    }

}
