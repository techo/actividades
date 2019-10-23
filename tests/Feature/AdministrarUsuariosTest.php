<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class AdministrarUsuariosTest extends TestCase
{
	use RefreshDatabase;

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
            'sexo' => $jose->sexo,
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
    public function administrador_puede_eliminar_usuario()
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
    public function administrador_puede_fundir_cuentas()
    {
        $this->withoutExceptionHandling();
        $this->seed('PermisosSeeder');

        $admin = factory('App\Persona')->create();
        $admin->assignRole('admin');

        $agus = factory('App\Persona')->create();
        $agus_segunda_cuenta = factory('App\Persona')->create();

        $actividad_1 = app(ActividadFactory::class)
            ->coordinadaPor($agus)
            ->agregarInscripto($agus)
            ->agregarEvaluacionDePersona($agus)
            ->create();

        $actividad_2 = app(ActividadFactory::class)
            ->coordinadaPor($agus_segunda_cuenta)
            ->agregarInscripto($agus_segunda_cuenta)
            ->agregarEvaluacionDePersona($agus_segunda_cuenta)
            ->create();

        $this->actingAs($admin)
            ->post('/admin/usuarios/' . $agus->idPersona . '/fundir/' . $agus_segunda_cuenta->idPersona)
            ->assertOk();

        $this->assertTrue($agus->inscripciones()->count() == 2);
        $this->assertTrue($agus->evaluacionesRecibidas()->count() == 2);
        $this->assertTrue($agus->actividades()->count() == 2);

        $this->assertSoftDeleted('Persona', [
            'idPersona' => $agus_segunda_cuenta->idPersona,
        ]);
    }

}
