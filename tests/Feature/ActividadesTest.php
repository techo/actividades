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
    	$permission = Permission::create(['name' => 'ver_backoffice']);
		$persona->givePermissionTo($permission);

    	$actividad = factory('App\Actividad')->make();

    	$actividad_t['pais']['id'] = $actividad->idPais;
        $actividad_t['provincia']['id'] = $actividad->idProvincia;
        $actividad_t['localidad']['id'] = $actividad->idLocalidad;
        $actividad_t['coordinador']['idPersona'] = $actividad->idCoordinador;
        $actividad_t['oficina']['id'] = $actividad->idOficina;
        $actividad_t['tipo']['categoria']['id'] = $actividad->tipo->categoria->id;
        $actividad_t['puntosEncuentroBorrados'] = [];
        $actividad_t['puntosEncuentroEditados'] = [];

        $actividad_t = array_merge($actividad_t,$actividad->toArray());

        $punto = factory('App\PuntoEncuentro')->make();
        $punto->nuevo = true;
        $punto->responsable = $punto->idPersona;

        $actividad_t['puntos_encuentro'] = [ $punto->toArray() ];

        $this->actingAs($persona)
        	->post('/admin/actividades/crear', $actividad_t)
        	->assertSeeText("Actividad guardada correctamente.");

        $this->assertDatabaseHas('Actividad', [ 'nombreActividad' => $actividad->nombreActividad])
        	->assertDatabaseHas('PuntoEncuentro', [ 'punto' => $punto->punto ]);
    }
}
