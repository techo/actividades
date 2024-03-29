<?php

namespace Tests\Feature;

use App\ActividadFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdministrarUsuariosTest extends TestCase
{
	use RefreshDatabase;

    /** @test */
    public function administrador_puede_crear_usuario()
    {
        $this->withoutExceptionHandling();

        $this->seed('PermisosSeeder');

        $admin = factory('App\Persona')->create();
        $admin->assignRole('admin');

        $jose = factory('App\Persona')->make();

        $datos = [
            'idUsuario' => $jose->idPersona,
            'nombre' => $jose->nombres,
            'apellido' => $jose->apellidoPaterno,
            'rol' => $jose->rol,
            'pais' => [ 'id' => $jose->idPais],
            'genero' => $jose->genero,
            'nacimiento' => $jose->fechaNacimiento,
            'telefono' => $jose->telefonoMovil,
            'dni' => $jose->dni,
            'email' => $jose->mail,
            'rol' => [ 'rol' => 'usuario_autenticado'],
            'password' => 'contraseña',
            'password_confirmation' => 'contraseña'
            ];
        
        $this->actingAs($admin)
            ->post('/admin/usuarios/registrar', $datos)
            ->assertStatus(200);

        $this->assertDatabaseHas('Persona', [ 'nombres' => $jose->nombres ]);
    }

    /** @test */
    public function administrador_puede_editar_usuario()
    {
    	$this->withoutExceptionHandling();

        $this->seed('PermisosSeeder');

		$admin = factory('App\Persona')->create();
        $admin->assignRole('admin');

		$jose = factory('App\Persona')->create();

		$jose->nombres = "Modificado";

		$datos = [
            'idUsuario' => $jose->idPersona,
            'nombre' => $jose->nombres,
            'apellido' => $jose->apellidoPaterno,
            'rol' => $jose->rol,
            'pais' => [ 'id' => $jose->idPais],
            'genero' => $jose->genero,
            'nacimiento' => $jose->fechaNacimiento,
            'telefono' => $jose->telefonoMovil,
            'dni' => $jose->dni,
            'email' => $jose->mail,
            'rol' => [ 'rol' => 'usuario_autenticado'],
            'password' => 'contraseña',
            'password_confirmation' => 'contraseña'
            ];
		
		$this->actingAs($admin)
        	->post('/admin/usuarios/' . $jose->idPersona . '/editar', $datos)
        	->assertSee('Usuario editado correctamente')
        	->assertStatus(200);

        $this->assertDatabaseHas('Persona', [ 'nombres' => 'Modificado' ]);
    }

    /** @test */
    public function administrador_puede_eliminar_usuario_y_volver_a_crear_con_mismo_email()
    {
        $this->withoutExceptionHandling();

        $this->seed('PermisosSeeder');

        $admin = factory('App\Persona')->create();
        $admin->assignRole('admin');

        $jose = factory('App\Persona')->create();

        $this->actingAs($admin)
            ->delete('/admin/usuarios/' . $jose->idPersona)
            ->assertStatus(302);

        $this->assertSoftDeleted('Persona', [ 'idPersona' => $jose->idPersona ]);

        $datos = [
            "email" => $jose->mail,
            "dni" => $jose->dni,
            "nombre" => $jose->nombres,
            "apellido" => $jose->apellidoPaterno,
            "rol" => 0,
            "pais" => [ "id" => $jose->idPais ],
            "genero" => $jose->genero,
            "nacimiento" => $jose->fechaNacimiento,
            "telefono" => $jose->telefonoMovil,
        ];

        $this->actingAs($admin)
            ->post('/admin/usuarios/registrar' , $datos)
            ->assertStatus(200);
    }

    /** @test */
    public function coordinador_no_puede_eliminar_usuario()
    {
        //$this->withoutExceptionHandling();

        $this->seed('PermisosSeeder');

        $coordi = factory('App\Persona')->create();
        $coordi->assignRole('coordinador');

        $jose = factory('App\Persona')->create();

        $this->actingAs($coordi)
            ->delete('/admin/usuarios/' . $jose->idPersona)
            ->assertForbidden();

        $this->assertDatabaseHas('Persona', [
            'idPersona' => $jose->idPersona,
            'deleted_at' => null
        ]);

    }

    public function crear_usuario_caracter_internacional()
     {
        $this->withoutExceptionHandling();

        $actividades = factory('App\Actividad', 4)->create();

        $persona = factory('App\Persona')->create(['nombres' => 'ñombré']);
        $persona->givePermissionTo(Permission::create(['name' => 'ver_backoffice']));

        $this->actingAs($persona)
            ->get('/admin/ajax/search/usuarios?usuario=ñombré')
             ->assertStatus(200)
             ->assertJsonStructure(
                 [
                   '*' => [
                     "idPersona",
                     "dni",
                     "nombre"
                   ]
                 ]
             );
     }

    /** @test */
    public function administrador_puede_fusionar_cuentas()
    {
        //$this->withoutExceptionHandling();
        $this->seed('PermisosSeeder');

        $admin = factory('App\Persona')->create();
        $admin->assignRole('admin');

        $agus = factory('App\Persona')->create();
        $agus_segunda_cuenta = factory('App\Persona')->create();

        $actividad_1 = app(ActividadFactory::class)
            ->creadaPor($agus)
            ->coordinadaPor($agus)
            ->agregarInscripto($agus)
            ->agregarEvaluacionDePersona($agus)
            ->agregarEvaluacion($agus)
            ->conGrupoRaiz([ $agus ])
            ->create();

        $actividad_2 = app(ActividadFactory::class)
            ->creadaPor($agus_segunda_cuenta)
            ->coordinadaPor($agus_segunda_cuenta)
            ->agregarInscripto($agus_segunda_cuenta)
            ->agregarEvaluacionDePersona($agus_segunda_cuenta)
            ->agregarEvaluacion($agus_segunda_cuenta)
            ->conGrupoRaiz([ $agus_segunda_cuenta, $agus ])
            ->create();

        $datos = [ 'idPersona' => $agus_segunda_cuenta->idPersona ];
        $this->actingAs($admin)
            ->post('/admin/usuarios/' . $agus->idPersona . '/fusionar', $datos) ->assertOk();

        $this->assertTrue($agus->inscripciones()->count() == 2);
        $this->assertTrue($agus->evaluacionesRecibidas()->count() == 2);
        $this->assertTrue($agus->actividades()->count() == 2);
        $this->assertTrue($agus->actividadesCreadas()->count() == 2);
        $this->assertTrue($agus->evaluacionesActividadRealizadas()->count() == 2);
        $this->assertTrue($agus->gruposRoles()->count() == 3);

        $this->assertSoftDeleted('Persona', [
            'idPersona' => $agus_segunda_cuenta->idPersona,
        ]);

        //test no se puede fusionar un usuario consigo mismo
        $datos = [ 'idPersona' => $agus->idPersona ];
        $this->actingAs($admin)
            ->post('/admin/usuarios/' . $agus->idPersona . '/fusionar', $datos)
            ->assertSessionHasErrors();
    }

}
