<?php

namespace Tests\Feature;

use App\ActividadFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Config;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ActividadesTest extends TestCase
{
	use RefreshDatabase;

    /** @test */
    public function usuario_puede_ver_actividades_de_un_pais()
    {
        $this->withoutExceptionHandling();
        $this->seed('PermisosSeeder');

        $francia = factory('App\Pais')->create();
        $argentina = factory('App\Pais')->create();

        $actividad = app(ActividadFactory::class)
            ->agregarPuntoConInscriptos(0)
            ->conPais($francia->id)
            ->create();

        $actividad_2 = app(ActividadFactory::class)
            ->agregarPuntoConInscriptos(0)
            ->conPais($argentina->id)
            ->create();

        Config::set('app.pais', $francia->id);
        $this->get('/ajax/actividades')->assertJsonCount(1, 'data');

        Config::set('app.pais', $argentina->id);
        $this->get('/ajax/actividades')->assertJsonCount(1, 'data');
    }

    /** @test */
    public function usuario_puede_ver_provincias_y_localidades_segun_pais()
    {
        $this->withoutExceptionHandling();
        $this->seed('PermisosSeeder');

        $francia = factory('App\Pais')->create();
        $argentina = factory('App\Pais')->create();

        $actividad = app(ActividadFactory::class)
            ->agregarPuntoConInscriptos(0)
            ->conPais($francia)
            ->create();

        $actividad_2 = app(ActividadFactory::class)
            ->agregarPuntoConInscriptos(0)
            ->conPais($argentina)
            ->create();

        Config::set('app.pais', $francia->id);
        $this
            ->get('/ajax/actividades/provincias')
            ->assertJsonCount(1);

        Config::set('app.pais', $argentina->id);
        $this
            ->get('/ajax/actividades/provincias')
            ->assertJsonCount(1);
    }

    /** @test */
    public function usuario_puede_ver_tipos_segun_pais()
    {
        $this->withoutExceptionHandling();
        $this->seed('PermisosSeeder');

        $francia = factory('App\Pais')->create();
        $argentina = factory('App\Pais')->create();

        $actividad = app(ActividadFactory::class)
            ->agregarPuntoConInscriptos(0)
            ->conPais($francia)
            ->create();

        $actividad_2 = app(ActividadFactory::class)
            ->agregarPuntoConInscriptos(0)
            ->conPais($argentina)
            ->create();

        Config::set('app.pais', $francia->id);
        $this
            ->get('/ajax/actividades/tipos')
            ->assertJsonCount(1);

        Config::set('app.pais', $argentina->id);
        $this
            ->get('/ajax/actividades/tipos')
            ->assertJsonCount(1);
    }

	/** @test */
    public function usuario_puede_crear_actividad()
    {
    	$this->withoutExceptionHandling();

        $this->seed('PermisosSeeder');

    	$persona = factory('App\Persona')->create();
		$persona->assignRole('coordinador');

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
        $punto->estado = true;
        $punto->responsable = $punto->idPersona;

        $actividad_t['puntos_encuentro'] = [ $punto->toArray() ];
        $this->actingAs($persona)
        	->post('/admin/actividades/crear', $actividad_t)
        	->assertSeeText('Actividad creada correctamente')
            ->assertSessionHas('mensaje', 'Actividad creada correctamente');

        //funciona hacer un flash
        $this->actingAs($persona)
            ->get('/admin/actividades/usuario')
            ->assertSeeText('Actividad creada correctamente')
            ->assertSessionMissing('mensaje');

        //no se debería ver más el mensaje
        $this->actingAs($persona)
            ->get('/admin/actividades/usuario')
            ->assertDontSeeText('Actividad creada correctamente')
            ->assertSessionMissing('mensaje');

        $this->assertDatabaseHas('Actividad', [ 'nombreActividad' => $actividad->nombreActividad])
        	->assertDatabaseHas('PuntoEncuentro', [ 'punto' => $punto->punto ]);
    }

    /** @test */
    public function usuario_puede_listar_actividades_con_badge_esperar()
    {
        $this->withoutExceptionHandling();

        $this->seed('PermisosSeeder');

        $mati = factory('App\Persona')->create();

        $actividad = app(ActividadFactory::class)
            ->agregarPuntoConInscriptos(0)
            ->conEstado('con confirmacion')
            ->create();

        $i = factory('App\Inscripcion')->create([
            'idActividad' => $actividad->idActividad,
            'idPuntoEncuentro' => $actividad->puntosEncuentro[0]->idPuntoEncuentro,
            'idPersona' => $mati->idPersona,
        ]);

        $this->actingAs($mati)
            ->get('/ajax/actividades')
            ->assertSeeText("waiting_for_confirmation");

    }

    /** @test */
    public function usuario_puede_listar_actividades_con_badge_confirmado()
    {
        $this->withoutExceptionHandling();

        $this->seed('PermisosSeeder');

        $mati = factory('App\Persona')->create();

        $actividad = app(ActividadFactory::class)
            ->agregarPuntoConInscriptos(0)
            ->conEstado('con confirmacion y pago')
            ->create();

        $i = factory('App\Inscripcion')->create([
            'idActividad' => $actividad->idActividad,
            'idPuntoEncuentro' => $actividad->puntosEncuentro[0]->idPuntoEncuentro,
            'idPersona' => $mati->idPersona,
            'confirma' => 1,
            'pago' => 1,
        ]);

        $this->actingAs($mati)
            ->get('/ajax/actividades')
            ->assertSeeText("confirmed");
    }

    /** @test */
    public function usuario_puede_listar_actividades_con_badge_confirmar()
    {
        $this->withoutExceptionHandling();

        $this->seed('PermisosSeeder');

        $mati = factory('App\Persona')->create();

        $actividad = app(ActividadFactory::class)
            ->agregarPuntoConInscriptos(0)
            ->conEstado('con pago')
            ->create();

        $i = factory('App\Inscripcion')->create([
            'idActividad' => $actividad->idActividad,
            'idPuntoEncuentro' => $actividad->puntosEncuentro[0]->idPuntoEncuentro,
            'idPersona' => $mati->idPersona,
            'pago' => 0,
        ]);

        $this->actingAs($mati)
            ->get('/ajax/actividades')
            ->assertSeeText("confirm");
    }

}
