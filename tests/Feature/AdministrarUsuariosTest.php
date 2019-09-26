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
}
