<?php

namespace Tests\Feature\api;

use App\ActividadFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;

/**
 * Tarea #15 — Tests de la API mobile: listado y detalle de actividades.
 * Son los endpoints más consumidos por la app y los detectores de regresión
 * más importantes durante el upgrade (formato del JSON que la app espera).
 */
class ActividadesApiTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function actividades_general_devuelve_estructura_de_paginacion()
    {
        $this->seed('PermisosSeeder');

        app(ActividadFactory::class)->agregarPuntoConInscriptos(0)->create();

        $this->getJson('/api/actividadesGeneral')
            ->assertStatus(200)
            ->assertJsonStructure([
                'current_page', 'data', 'first_page_url', 'from', 'last_page',
                'last_page_url', 'next_page_url', 'path', 'per_page',
                'prev_page_url', 'to', 'total',
            ])
            ->assertJsonCount(1, 'data');
    }

    /** @test */
    public function detalle_de_actividad_devuelve_los_campos_que_la_app_espera()
    {
        $this->seed('PermisosSeeder');
        Passport::actingAs(factory('App\Persona')->create());

        $actividad = app(ActividadFactory::class)->agregarPuntoConInscriptos(0)->create();

        $this->getJson('/api/actividades/' . $actividad->idActividad)
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'idActividad', 'nombreActividad', 'descripcion', 'pago',
                    'fecha', 'hora', 'fechaInicio', 'fechaFin', 'lugar', 'moneda',
                    'puntosEncuentro', 'estadoInscripcion', 'cantInscriptos',
                    'cuposRestantes', 'requiere_ficha_medica', 'requiere_estudios',
                ],
            ])
            ->assertJson([ 'data' => [ 'idActividad' => $actividad->idActividad ] ]);
    }

    /**
     * CONTRATO DE FECHAS: la app espera las fechas formateadas dd-mm-aaaa.
     * El ActividadResource las formatea explícitamente con ->format('d-m-Y'),
     * así que NO dependen de la serialización por defecto (que Laravel 7 cambia
     * a ISO-8601). Este test fija ese contrato como ancla anti-regresión.
     * @test
     */
    public function el_detalle_formatea_las_fechas_en_formato_dd_mm_aaaa()
    {
        $this->seed('PermisosSeeder');
        Passport::actingAs(factory('App\Persona')->create());

        $actividad = app(ActividadFactory::class)->agregarPuntoConInscriptos(0)->create();

        $data = $this->getJson('/api/actividades/' . $actividad->idActividad)
            ->assertStatus(200)
            ->json('data');

        $this->assertRegExp('/^\d{2}-\d{2}-\d{4}$/', $data['fechaInicio']);
        $this->assertRegExp('/^\d{2}-\d{2}-\d{4}$/', $data['fechaFin']);
        $this->assertRegExp('#^\d{2}/\d{2}$#', $data['fecha']);   // 'd/m'
        $this->assertRegExp('/^\d{2}:\d{2}$/', $data['hora']);    // 'H:i'
    }

    /** @test */
    public function detalle_de_actividad_inexistente_devuelve_404()
    {
        $this->seed('PermisosSeeder');
        Passport::actingAs(factory('App\Persona')->create());

        $this->getJson('/api/actividades/999999')
            ->assertStatus(404);
    }

    /** @test */
    public function filtra_actividades_por_categoria()
    {
        $this->seed('PermisosSeeder');
        Passport::actingAs(factory('App\Persona')->create());

        // 'construcciones' mapea (hardcodeado en routes/api.php) a tipos [11, 27, ...].
        $tipo = factory('App\Tipo')->create([ 'idTipo' => 11 ]);
        $actividad = app(ActividadFactory::class)
            ->deTipo($tipo->idTipo)
            ->agregarPuntoConInscriptos(0)
            ->create();

        $this->getJson('/api/actividades/categoria/construcciones')
            ->assertStatus(200)
            ->assertJsonFragment([ 'idActividad' => $actividad->idActividad ]);
    }

    /** @test */
    public function categoria_inexistente_devuelve_404()
    {
        $this->seed('PermisosSeeder');
        Passport::actingAs(factory('App\Persona')->create());

        $this->getJson('/api/actividades/categoria/no-existe')
            ->assertStatus(404);
    }
}
