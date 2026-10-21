<?php

namespace Tests\Feature;

use App\Actividad;
use App\ActividadFactory;
use App\ListadoPreferencia;
use App\Services\Listados\EnriquecedorFilas;
use App\Services\Listados\OrdenListado;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Reclamos de oct-2026 sobre el listado de inscripciones: orden estable y recordado
 * (#23/#24/#27), link al perfil (#28/#29) y definición de participaciones (#14).
 */
class ListadoInscripcionesReclamosTest extends TestCase
{
    use RefreshDatabase;

    private function admin()
    {
        $admin = factory('App\Persona')->create();
        $admin->assignRole('admin');

        return $admin;
    }

    /** @test */
    public function el_orden_es_estable_con_empates_y_respeta_el_multi_sort()
    {
        $this->seed('PermisosSeeder');
        $admin = $this->admin();
        $actividad = app(ActividadFactory::class)->creadaPor($admin)->agregarPuntoConInscriptos(0)->create();
        $punto = $actividad->puntosEncuentro->first();

        // Mismo segundo de inscripción para todos: antes el orden entre ellos era arbitrario.
        $ids = [];
        foreach (['Bravo', 'Alfa', 'Alfa'] as $apellido) {
            $persona = factory('App\Persona')->create(['apellidoPaterno' => $apellido]);
            $ids[] = factory('App\Inscripcion')->create([
                'idActividad' => $actividad->idActividad,
                'idPuntoEncuentro' => $punto->idPuntoEncuentro,
                'idPersona' => $persona->idPersona,
                'fechaInscripcion' => '2026-10-01 10:00:00',
            ])->idInscripcion;
        }

        $data = $this->actingAs($admin)
            ->getJson('/admin/ajax/actividades/' . $actividad->idActividad . '/inscripciones/?sort=apellidoPaterno|asc,fechaInscripcion|desc')
            ->assertStatus(200)
            ->json('data');

        // Alfa, Alfa (desempate por idInscripcion desc), Bravo.
        $this->assertEquals([$ids[2], $ids[1], $ids[0]], collect($data)->pluck('id')->all());
    }

    /** @test */
    public function el_orden_elegido_se_recuerda_y_se_usa_en_otra_actividad()
    {
        $this->seed('PermisosSeeder');
        $admin = $this->admin();
        $actividad = app(ActividadFactory::class)->creadaPor($admin)->agregarPuntoConInscriptos(1)->create();

        $this->actingAs($admin)
            ->getJson('/admin/ajax/actividades/' . $actividad->idActividad . '/inscripciones/?sort=apellidoPaterno|asc')
            ->assertStatus(200);

        $this->assertEquals('apellidoPaterno|asc', ListadoPreferencia::where('persona_id', $admin->idPersona)
            ->where('context_id', $actividad->idActividad)->value('orden'));

        // La fila creada solo por el orden no pisa las columnas (null = defaults).
        $this->actingAs($admin)
            ->getJson('/admin/ajax/listados/inscripciones/' . $actividad->idActividad . '/config')
            ->assertStatus(200)
            ->assertJson(['preferencias' => null]);

        // Otra actividad sin orden propio: hereda el último que usó.
        $sortOrder = OrdenListado::sortOrderInicial($admin->idPersona, 'inscripciones', 999999, [['sortField' => 'x']], config('datatables.inscripciones.fijas'));
        $this->assertEquals([[
            'field' => '__component:celda-persona-link',
            'sortField' => 'apellidoPaterno',
            'direction' => 'asc',
        ]], $sortOrder);
    }

    /** @test */
    public function sin_orden_guardado_usa_el_default()
    {
        $persona = factory('App\Persona')->create();
        $default = config('datatables.inscripciones.sortOrder');

        $this->assertEquals($default, OrdenListado::sortOrderInicial($persona->idPersona, 'inscripciones', 1, $default, []));
    }

    /** @test */
    public function nombre_y_apellido_linkean_al_perfil_y_whatsapp_es_default()
    {
        $fijas = collect(config('datatables.inscripciones.fijas'))->keyBy('sortField');
        $this->assertEquals('__component:celda-persona-link', $fijas['nombres']['name']);
        $this->assertEquals('apellidoPaterno', $fijas['apellidoPaterno']['atributo']);
        $this->assertContains('whatsapp', config('datatables.inscripciones.defaults'));
    }

    /** @test */
    public function participaciones_cuenta_actividades_distintas_con_presente_y_no_borradas()
    {
        $persona = factory('App\Persona')->create();
        $actividad = factory('App\Actividad')->create();

        // Duplicada en la misma actividad: cuenta 1.
        factory('App\Inscripcion', 2)->create(['idPersona' => $persona->idPersona, 'idActividad' => $actividad->idActividad, 'presente' => 1]);
        // Otra actividad, presente: cuenta.
        factory('App\Inscripcion')->create(['idPersona' => $persona->idPersona, 'presente' => 1]);
        // Ausente: no cuenta.
        factory('App\Inscripcion')->create(['idPersona' => $persona->idPersona, 'presente' => 0]);
        // Actividad borrada: no cuenta.
        $borrada = factory('App\Actividad')->create();
        factory('App\Inscripcion')->create(['idPersona' => $persona->idPersona, 'idActividad' => $borrada->idActividad, 'presente' => 1]);
        Actividad::where('idActividad', $borrada->idActividad)->update(['deleted_at' => now()]);

        $filas = collect([new \App\Persona(['idPersona' => $persona->idPersona])]);
        $filas->first()->idPersona = $persona->idPersona;
        (new EnriquecedorFilas)->inyectarMetricasVoluntario($filas, 'idPersona');

        $this->assertEquals(2, $filas->first()->participaciones);
    }
}
