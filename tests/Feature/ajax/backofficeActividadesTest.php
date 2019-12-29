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
}

