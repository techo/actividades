<?php

namespace Tests\Feature;

use App\Mail\MailConfimacionInscripcion;
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

        $persona = factory('App\Persona')->create();
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

        $this->assertDatabaseHas('Inscripcion', [
        'idPuntoEncuentro' => $i->idPuntoEncuentro,
        'idActividad' => $i->idActividad,
        'idPersona' => $i->idPersona,
        'estado' => 'Desinscripto'
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
            ->create();

        $maria = factory('App\Persona')->create([
            'recibirMails' => 1
        ]);

        $datos = [
            'idActividad' => $actividad->idActividad,
            'idPersona' => $maria->idPersona,
            'idPuntoEncuentro' => $actividad->puntosEncuentro->first()->idPuntoEncuentro,
            'idGrupo' => 0,
            'rol' => '',
        ];


        $this->actingAs($admin)
            ->post('/admin/ajax/actividades/'. $actividad->idActividad .'/inscripciones', $datos)
            ->assertStatus(200);

        $this->assertDatabaseHas('Inscripcion', [
            'idPuntoEncuentro' => $actividad->inscripciones->first()->idPuntoEncuentro,
            'idActividad' => $actividad->inscripciones->first()->idActividad,
            'idPersona' => $actividad->inscripciones->first()->idPersona
        ]);

        Mail::assertQueued(MailConfimacionInscripcion::class, 0);
    }

    /** @test */
    public function administrador_puede_re_inscribir_usuario()
    {
        $this->withoutExceptionHandling(); 

        $this->seed('PermisosSeeder');

        $admin = factory('App\Persona')->create();
        $admin->assignRole('admin');

        $actividad = app(ActividadFactory::class)
            ->creadaPor($admin)
            ->agregarPuntoConInscriptos(0)
            ->create();

        $maria = factory('App\Persona')->create();

        $datos = [
            'idActividad' => $actividad->idActividad,
            'idPersona' => $maria->idPersona,
            'idPuntoEncuentro' => $actividad->puntosEncuentro->first()->idPuntoEncuentro,
            'idGrupo' => 0,
            'rol' => ''
        ];

        $inscripcion_eliminada = factory('App\Inscripcion')->create([
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
    public function administrador_no_puede_ver_iscriptos_eliminados()
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
            ->get('/admin/ajax/actividades/'. $actividad->idActividad 
                .'/inscripciones')
            ->assertStatus(200);

        $this->assertTrue(count(json_decode($response->getContent())->data) == 4);

    }

    /** @test */
    public function administrador_puede_desinscribir_usuario()
    {
        $this->withoutExceptionHandling(); 

        $this->seed('RolesTableSeeder');
        $this->seed('PermissionsTableSeeder');
        $this->seed('RolePermissionsSeeder');

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

        $this->assertDatabaseHas('Inscripcion', [
            'idPuntoEncuentro' => $actividad->inscripciones->first()->idPuntoEncuentro,
            'idActividad' => $actividad->inscripciones->first()->idActividad,
            'idPersona' => $actividad->inscripciones->first()->idPersona,
            'estado' => 'Desinscripto'
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

        $this->seed('RolesTableSeeder');
        $this->seed('PermissionsTableSeeder');
        $this->seed('RolePermissionsSeeder');

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

        $this->assertTrue($actividad->inscripciones()->where(['estado' => 'Desinscripto'])->count() == 5);

    }
}
