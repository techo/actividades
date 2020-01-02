<?php

namespace Tests\Feature\ajax;

use App\ActividadFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class backofficeActividadesTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function listar_todas_las_actividades()
    {
        $this->withoutExceptionHandling();
        
        $actividades = factory('App\Actividad', 4)->create();

        $persona = factory('App\Persona')->create();
        $persona->givePermissionTo(Permission::create(['name' => 'ver_backoffice']));

        $this->actingAs($persona)
            ->get('/admin/ajax/actividades?sort=nombreActividad|asc')
            ->assertStatus(200)
            ->assertJsonStructure(
                [
                    "current_page",
                    "data",
                    "first_page_url",
                    "from",
                    "last_page",
                    "last_page_url",
                    "next_page_url",
                    "path",
                    "per_page",
                    "prev_page_url",
                    "to",
                    "total"
                ]
            );
    }

    /** @test */
    public function usuario_puede_crear_actividad()
    {
        $this->withoutExceptionHandling();

        $this->seed('PermisosSeeder');

        $persona = factory('App\Persona')->create();
        $persona->assignRole('coordinador');

        $actividad = factory('App\Actividad')->make();

        $this->actingAs($persona)
            ->post('/admin/actividades/crear', $actividad->toArray())
            ->assertJsonFragment([ 'nombreActividad' => $actividad->nombreActividad ]);

        $this->assertDatabaseHas('Actividad', [ 'nombreActividad' => $actividad->nombreActividad])
            ->assertDatabaseHas('PuntoEncuentro', [ 
                'punto' => $actividad->lugar, 
                'idPais' => $actividad->idPais, 
                'idPersona' => $persona->idPersona,
                'horario' => $actividad->fechaInicio->format('H:i'), 
            ]);
    }

    /** @test */
    public function usuario_puede_crear_actividad_sin_fechas_explicitas()
    {
        //$this->withoutExceptionHandling();

        $this->seed('PermisosSeeder');

        $persona = factory('App\Persona')->create();
        $persona->assignRole('coordinador');

        $actividad = factory('App\Actividad')->make();

        $a = $actividad->toArray();

        unset($a['fechaInicioInscripciones']);
        unset($a['fechaFinInscripciones']);
        unset($a['fechaInicioEvaluaciones']);
        unset($a['fechaFinEvaluaciones']);

        $this->actingAs($persona)
            ->post('/admin/actividades/crear', $a)
            ->assertSessionHasNoErrors();

    }

    /** @test */
    public function usuario_no_puede_crear_actividad_con_fechas_explicitas_vacias()
    {
        $this->seed('PermisosSeeder');

        $persona = factory('App\Persona')->create();
        $persona->assignRole('coordinador');

        $actividad = factory('App\Actividad')->make();

        $a = $actividad->toArray();

        $a['fechaInicioInscripciones'] = null;
        $a['fechaInicioEvaluaciones'] = null;

        $this->actingAs($persona)
            ->post('/admin/actividades/crear', $a)
            ->assertSessionHasErrors();
    }

    /** @test */
    public function usuario_no_puede_crear_actividad_con_fechas_incompletas()
    {
        $this->seed('PermisosSeeder');

        $persona = factory('App\Persona')->create();
        $persona->assignRole('coordinador');

        $actividad = factory('App\Actividad')->make();

        $a = $actividad->toArray();

        unset($a['fechaFinInscripciones']);
        unset($a['fechaFinEvaluaciones']);

        $this->actingAs($persona)
            ->post('/admin/actividades/crear', $a)
            ->assertSessionHasErrors();
    }

    /** @test */
    public function usuario_no_puede_crear_actividad_con_fechas_incompletas_2()
    {
        $this->seed('PermisosSeeder');

        $persona = factory('App\Persona')->create();
        $persona->assignRole('coordinador');

        $actividad = factory('App\Actividad')->make();

        $a = $actividad->toArray();

        unset($a['fechaInicioInscripciones']);
        unset($a['fechaInicioEvaluaciones']);

        $this->actingAs($persona)
            ->post('/admin/actividades/crear', $a)
            ->assertSessionHasNoErrors();
    }

    /** @test */
    public function usuario_no_puede_crear_actividad_con_fechas_incorrectas()
    {
        $this->seed('PermisosSeeder');

        $persona = factory('App\Persona')->create();
        $persona->assignRole('coordinador');

        $actividad = app(ActividadFactory::class)
            ->conEstado('fechas explicitas incorrectas')
            ->create();

        $this->actingAs($persona)
            ->post('/admin/actividades/crear', $actividad->toArray())
            ->assertSessionHasErrors();
    }

    /** @test */
    public function usuario_puede_crear_actividad_sin_pago()
    {
        $this->seed('PermisosSeeder');

        $persona = factory('App\Persona')->create();
        $persona->assignRole('coordinador');

        $actividad = factory('App\Actividad')->make([ 
            'pago' => "0",
            'montoMin' => "0.00"
        ]);

        $a = $actividad->toArray();

        $this->actingAs($persona)
            ->post('/admin/actividades/crear', $a)
            ->assertSessionHasNoErrors();
    }

    /** @test */
    public function usuario_puede_crear_actividad_con_pago()
    {
        $this->seed('PermisosSeeder');

        $persona = factory('App\Persona')->create();
        $persona->assignRole('coordinador');

        $actividad = factory('App\Actividad')->make([ 
            'pago' => "0",
        ]);

        $a = $actividad->toArray();

        unset($a['montoMin']);

        $this->actingAs($persona)
            ->post('/admin/actividades/crear', $a)
            ->assertSessionHasNoErrors();

        $actividad = factory('App\Actividad')->make([ 
            'pago' => "1",
            //'montoMin' => "0.00"
        ]);

        $a = $actividad->toArray();

        unset($a['montoMin']);

        $this->actingAs($persona)
            ->post('/admin/actividades/crear', $a)
            ->assertSessionHasErrors();
    }

    /** @test */
    public function crear_punto_encuentro()
    {
        $this->withoutExceptionHandling();
        $this->seed('PermisosSeeder');

        $admin = factory('App\Persona')->create();
        $admin->assignRole('admin');

        $actividad = app(ActividadFactory::class)
            ->agregarPuntoConInscriptos(0)
            ->create();

        $punto = factory('App\PuntoEncuentro')->make();

        $this->actingAs($admin)
            ->post('/admin/ajax/actividades/' . $actividad->idActividad . '/puntos', $punto->toArray())
            ->assertJsonFragment([ 'punto' => $punto->punto ]);
    }

    /** @test */
    public function editar_punto_encuentro()
    {
        $this->withoutExceptionHandling();
        $this->seed('PermisosSeeder');

        $admin = factory('App\Persona')->create();
        $admin->assignRole('admin');

        $actividad = app(ActividadFactory::class)
            ->agregarPuntoConInscriptos(0)
            ->create();

        $punto = $actividad->PuntosEncuentro[0];

        $punto->punto = "modificado";

        $this->actingAs($admin)
            ->post('/admin/ajax/actividades/' . $actividad->idActividad . '/puntos', $punto->toArray())
            ->assertJsonFragment([ 'punto' => $punto->punto ]);
    }

    /** @test */
    public function eliminar_punto_encuentro()
    {
        $this->withoutExceptionHandling();
        $this->seed('PermisosSeeder');

        $admin = factory('App\Persona')->create();
        $admin->assignRole('admin');

        $actividad = app(ActividadFactory::class)
            ->agregarPuntoConInscriptos(0)
            ->create();

        $punto = $actividad->PuntosEncuentro[0];

        $this->actingAs($admin)
            ->delete('/admin/ajax/actividades/' . $actividad->idActividad . '/puntos/' . $punto->idPuntoEncuentro)
            ->assertStatus(200);
    }

    /** @test */
    public function no_se_puede_eliminar_punto_encuentro_con_inscriptos()
    {
        $this->withoutExceptionHandling();
        $this->seed('PermisosSeeder');

        $admin = factory('App\Persona')->create();
        $admin->assignRole('admin');

        $actividad = app(ActividadFactory::class)
            ->agregarPuntoConInscriptos(1)
            ->create();

        $punto = $actividad->PuntosEncuentro[0];

        $this->actingAs($admin)
            ->delete('/admin/ajax/actividades/' . $actividad->idActividad . '/puntos/' . $punto->idPuntoEncuentro)
            ->assertStatus(422);
    }

    /** @test */
    public function no_se_puede_ver_sin_ser_creador_o_coordinador_o_administrador()
    {
        //$this->withoutExceptionHandling();
        $this->seed('PermisosSeeder');

        $migue = factory('App\Persona')->create();
        $migue->assignRole('coordinador');

        $actividad = app(ActividadFactory::class)
            ->agregarPuntoConInscriptos(1)
            ->create();

        $this->actingAs($migue)
            ->get('/admin/actividades/' . $actividad->idActividad)
            ->assertStatus(403);
    }

    /** @test */
    public function se_puede_si_es_coordinador()
    {
        //$this->withoutExceptionHandling();
        $this->seed('PermisosSeeder');

        $alber = factory('App\Persona')->create();
        $alber->assignRole('coordinador');

        $actividad = app(ActividadFactory::class)
            ->agregarPuntoConInscriptos(1)
            ->coordinadaPor($alber)
            ->create();

        $this->actingAs($alber)
            ->get('/admin/actividades/' . $actividad->idActividad)
            ->assertStatus(200);
    }

}

