<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class ActividadesTest extends TestCase
{
	use RefreshDatabase;

	/** @test */

    public function usuario_puede_crear_actividad()
    {
    	$this->withoutExceptionHandling();

    	$persona = factory('App\Persona')->create();
    	$actividad = factory('App\Actividad')->create();

    	dd($actividad);

		$permission = Permission::create(['name' => 'ver_backoffice']);

		$persona->givePermissionTo($permission);


        dd($this->actingAs($persona)
        	->post('/admin/actividades/crear', $actividad));
        	//->assertRedirect();
    }
}
