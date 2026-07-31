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

    /**
     * El home (index.vue) pide actividades categoría por categoría. Antes solo
     * recorría el set hardcodeado [1,2,3,5]; ahora recorre TODAS las categorías
     * de la DB. Este test fija que el endpoint que consume el home devuelve la
     * actividad cuando se filtra por una categoría fuera de ese set legacy.
     * @test
     */
    public function actividades_de_una_categoria_fuera_del_set_legacy_del_home_se_listan()
    {
        $this->withoutExceptionHandling();
        $this->seed('PermisosSeeder');

        $pais = factory('App\Pais')->create();

        // id 999 => explícitamente fuera del hardcode viejo [1,2,3,5].
        $categoria = factory('App\CategoriaActividad')->create(['id' => 999]);
        $tipo = factory('App\Tipo')->create(['idCategoria' => $categoria->id]);

        $actividad = app(ActividadFactory::class)
            ->deTipo($tipo->idTipo)
            ->agregarPuntoConInscriptos(0)
            ->conPais($pais->id)
            ->create();

        Config::set('app.pais', $pais->id);

        $this->get('/ajax/actividades?categoria=' . $categoria->id)
            ->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['idActividad' => $actividad->idActividad]);
    }

}
