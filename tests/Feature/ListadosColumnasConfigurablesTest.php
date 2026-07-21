<?php

namespace Tests\Feature;

use App\EvaluacionPersonaRespuesta;
use App\Inscripcion;
use App\ListadoColumna;
use App\Search\InscripcionesSearch;
use App\Services\Listados\EnriquecedorFilas;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use \App\ActividadFactory;

/**
 * Cobertura del sistema de listados con columnas configurables (inscripciones):
 * qué columnas se ofrecen, persistencia de la selección por usuario, columnas
 * de seguimiento + validación de valores, autorización/scoping y las métricas
 * del voluntario (participaciones/nivel/evaluación) + escapado de respuestas.
 */
class ListadosColumnasConfigurablesTest extends TestCase
{
    use RefreshDatabase;

    private function admin()
    {
        $admin = factory('App\Persona')->create();
        $admin->assignRole('admin');

        return $admin;
    }

    /** Actividad con pago + exención (para las columnas condicionales) y un punto con 1 inscripto. */
    private function actividadConInscripto($creador)
    {
        return app(ActividadFactory::class)
            ->creadaPor($creador)
            ->conEstado('con confirmacion y pago')
            ->agregarPuntoConInscriptos(1)
            ->create(['permite_exencion' => 1]);
    }

    /** @test */
    public function config_ofrece_los_grupos_y_las_columnas_condicionales()
    {
        $this->withoutExceptionHandling();
        $this->seed('PermisosSeeder');

        $admin = $this->admin();
        $actividad = $this->actividadConInscripto($admin);

        $data = $this->actingAs($admin)
            ->getJson('/admin/ajax/listados/inscripciones/' . $actividad->idActividad . '/config')
            ->assertStatus(200)
            ->json();

        $grupos = collect($data['grupos'])->keyBy('key');
        $this->assertTrue($grupos->has('datos_generales'));
        $this->assertTrue($grupos->has('ficha_medica'));
        $this->assertTrue($grupos->has('seguimiento'));

        $keysGenerales = collect($grupos['datos_generales']['campos'])->pluck('key');
        // Condicionales por pago/confirmación/exención + columnas nuevas.
        foreach (['confirma', 'pago', 'voucher', 'beca', 'whatsapp', 'participaciones', 'nivel', 'evaluacion_general'] as $key) {
            $this->assertTrue($keysGenerales->contains($key), "Falta la columna '$key' en datos_generales");
        }
        $keysFicha = collect($grupos['ficha_medica']['campos'])->pluck('key');
        $this->assertTrue($keysFicha->contains('documento'), "Falta la columna 'documento' en ficha_medica");
    }

    /** @test */
    public function las_columnas_condicionales_no_aparecen_si_la_actividad_no_las_usa()
    {
        $this->withoutExceptionHandling();
        $this->seed('PermisosSeeder');

        $admin = $this->admin();
        // Sin pago ni exención.
        $actividad = app(ActividadFactory::class)->creadaPor($admin)->agregarPuntoConInscriptos(1)->create();

        $data = $this->actingAs($admin)
            ->getJson('/admin/ajax/listados/inscripciones/' . $actividad->idActividad . '/config')
            ->assertStatus(200)
            ->json();

        $keysGenerales = collect($data['grupos'])->keyBy('key')['datos_generales']['campos'];
        $keys = collect($keysGenerales)->pluck('key');
        $this->assertFalse($keys->contains('voucher'), 'voucher no debería ofrecerse sin pago');
        $this->assertFalse($keys->contains('beca'), 'beca no debería ofrecerse sin exención');
    }

    /** @test */
    public function las_preferencias_persisten_por_usuario_y_contexto()
    {
        $this->withoutExceptionHandling();
        $this->seed('PermisosSeeder');

        $admin = $this->admin();
        $otro = $this->admin();
        $actividad = $this->actividadConInscripto($admin);
        $url = '/admin/ajax/listados/inscripciones/' . $actividad->idActividad;

        $this->actingAs($admin)
            ->putJson($url . '/preferencias', ['columnas' => ['dni', 'whatsapp', 'nivel']])
            ->assertStatus(200);

        $this->assertDatabaseHas('listado_preferencias', [
            'persona_id' => $admin->idPersona,
            'list_key' => 'inscripciones',
            'context_id' => $actividad->idActividad,
        ]);

        // El mismo usuario recupera su selección...
        $config = $this->actingAs($admin)->getJson($url . '/config')->json();
        $this->assertEquals(['dni', 'whatsapp', 'nivel'], $config['preferencias']);

        // ...y otro usuario NO ve esa preferencia (aislada por usuario).
        $configOtro = $this->actingAs($otro)->getJson($url . '/config')->json();
        $this->assertNull($configOtro['preferencias']);
    }

    /** @test */
    public function crear_columna_de_seguimiento_y_guardar_un_valor()
    {
        $this->withoutExceptionHandling();
        $this->seed('PermisosSeeder');

        $admin = $this->admin();
        $actividad = $this->actividadConInscripto($admin);
        $inscripcion = Inscripcion::where('idActividad', $actividad->idActividad)->firstOrFail();
        $url = '/admin/ajax/listados/inscripciones/' . $actividad->idActividad;

        $columna = $this->actingAs($admin)
            ->postJson($url . '/columnas', [
                'nombre' => 'Estado de contacto',
                'tipo' => 'estado',
                'opciones' => ['Contactado', 'En espera'],
            ])
            ->assertStatus(200)
            ->json('columna');

        $this->assertDatabaseHas('listado_columnas', [
            'id' => $columna['id'],
            'list_key' => 'inscripciones',
            'context_id' => $actividad->idActividad,
            'tipo' => 'estado',
        ]);

        $this->actingAs($admin)
            ->putJson($url . '/columnas/' . $columna['id'] . '/valores/' . $inscripcion->idInscripcion, ['valor' => 'Contactado'])
            ->assertStatus(200);

        $this->assertDatabaseHas('listado_columna_valores', [
            'columna_id' => $columna['id'],
            'record_id' => $inscripcion->idInscripcion,
            'valor' => 'Contactado',
        ]);

        // Vaciar el valor borra la fila.
        $this->actingAs($admin)
            ->putJson($url . '/columnas/' . $columna['id'] . '/valores/' . $inscripcion->idInscripcion, ['valor' => null])
            ->assertStatus(200);
        $this->assertDatabaseMissing('listado_columna_valores', [
            'columna_id' => $columna['id'],
            'record_id' => $inscripcion->idInscripcion,
        ]);
    }

    /** @test */
    public function un_valor_incoherente_con_el_tipo_se_rechaza()
    {
        $this->seed('PermisosSeeder');

        $admin = $this->admin();
        $actividad = $this->actividadConInscripto($admin);
        $inscripcion = Inscripcion::where('idActividad', $actividad->idActividad)->firstOrFail();

        $columna = ListadoColumna::create([
            'list_key' => 'inscripciones',
            'context_id' => $actividad->idActividad,
            'nombre' => 'Estado',
            'tipo' => 'estado',
            'opciones' => ['Contactado', 'En espera'],
            'orden' => 1,
            'created_by' => $admin->idPersona,
        ]);
        $url = '/admin/ajax/listados/inscripciones/' . $actividad->idActividad
            . '/columnas/' . $columna->id . '/valores/' . $inscripcion->idInscripcion;

        // Opción inexistente para un tipo "estado".
        $this->actingAs($admin)->putJson($url, ['valor' => 'OpcionInexistente'])->assertStatus(422);
        // Fecha con formato inválido.
        $fecha = ListadoColumna::create([
            'list_key' => 'inscripciones', 'context_id' => $actividad->idActividad,
            'nombre' => 'Fecha', 'tipo' => 'fecha', 'orden' => 2, 'created_by' => $admin->idPersona,
        ]);
        $this->actingAs($admin)
            ->putJson('/admin/ajax/listados/inscripciones/' . $actividad->idActividad . '/columnas/' . $fecha->id . '/valores/' . $inscripcion->idInscripcion, ['valor' => '31/12/2026'])
            ->assertStatus(422);
    }

    /** @test */
    public function sin_permiso_no_puede_ver_la_config()
    {
        $this->seed('PermisosSeeder');

        $admin = $this->admin();
        $actividad = $this->actividadConInscripto($admin);
        $random = factory('App\Persona')->create(); // sin rol ni permisos

        $this->actingAs($random)
            ->getJson('/admin/ajax/listados/inscripciones/' . $actividad->idActividad . '/config')
            ->assertStatus(403);
    }

    /** @test */
    public function list_key_desconocido_da_404()
    {
        $this->seed('PermisosSeeder');

        $admin = $this->admin();

        $this->actingAs($admin)
            ->getJson('/admin/ajax/listados/noexiste/1/config')
            ->assertStatus(404);
    }

    /** @test */
    public function no_permite_guardar_un_valor_de_un_registro_de_otra_actividad()
    {
        $this->seed('PermisosSeeder');

        $admin = $this->admin();
        $actividadA = $this->actividadConInscripto($admin);
        $actividadB = $this->actividadConInscripto($admin);
        $inscripcionDeB = Inscripcion::where('idActividad', $actividadB->idActividad)->firstOrFail();

        $columnaDeA = ListadoColumna::create([
            'list_key' => 'inscripciones',
            'context_id' => $actividadA->idActividad,
            'nombre' => 'Nota', 'tipo' => 'texto', 'orden' => 1, 'created_by' => $admin->idPersona,
        ]);

        // Intentar escribir el valor usando el contexto A pero un registro de B.
        $this->actingAs($admin)
            ->putJson('/admin/ajax/listados/inscripciones/' . $actividadA->idActividad . '/columnas/' . $columnaDeA->id . '/valores/' . $inscripcionDeB->idInscripcion, ['valor' => 'hola'])
            ->assertStatus(404);

        $this->assertDatabaseMissing('listado_columna_valores', [
            'columna_id' => $columnaDeA->id,
            'record_id' => $inscripcionDeB->idInscripcion,
        ]);
    }

    /** @test */
    public function el_enriquecedor_calcula_participaciones_nivel_y_evaluacion_y_escapa_respuestas()
    {
        $this->withoutExceptionHandling();
        $this->seed('PermisosSeeder');

        $admin = $this->admin();
        $persona = factory('App\Persona')->create();
        $actividad = app(ActividadFactory::class)
            ->creadaPor($admin)
            ->agregarPuntoConInscriptos(0)
            ->create();
        $punto = $actividad->puntosEncuentro->first();
        $inscripcion = factory('App\Inscripcion')->create([
            'idActividad' => $actividad->idActividad,
            'idPuntoEncuentro' => $punto->idPuntoEncuentro,
            'idPersona' => $persona->idPersona,
            'presente' => 1,
        ]);

        // 7 participaciones presentes en total (esta + 6 más) => Guardian.
        factory('App\Inscripcion', 6)->create(['idPersona' => $persona->idPersona, 'presente' => 1]);

        // Evaluación de competencias recibida: promedio (8 + 10) / 2 = 9.0
        $ev = factory('App\EvaluacionPersona')->create(['idEvaluado' => $persona->idPersona]);
        EvaluacionPersonaRespuesta::create(['idEvaluacionPersona' => $ev->idEvaluacionPersona, 'question_key' => 'conexion_equipo', 'score' => 8]);
        EvaluacionPersonaRespuesta::create(['idEvaluacionPersona' => $ev->idEvaluacionPersona, 'question_key' => 'compromiso_colaboracion', 'score' => 10]);

        // Respuesta a una pregunta con contenido peligroso (debe escaparse).
        $pregunta = \App\ActividadPregunta::create([
            'actividad_id' => $actividad->idActividad, 'pregunta' => '¿Comentario?', 'tipo' => 'abierta', 'requerida' => 0, 'orden' => 1,
        ]);
        \App\InscripcionRespuesta::create([
            'inscripcion_id' => $inscripcion->idInscripcion, 'pregunta_id' => $pregunta->id, 'respuesta' => '<script>alert(1)</script>',
        ]);

        $filas = InscripcionesSearch::query(['idActividad' => $actividad->idActividad])->get();
        $enriquecedor = new EnriquecedorFilas;
        $enriquecedor->enriquecer($filas, 'inscripciones', $actividad->idActividad, $actividad->idActividad);
        $enriquecedor->inyectarMetricasVoluntario($filas, 'idPersona');

        $fila = $filas->firstWhere('id', $inscripcion->idInscripcion);
        $this->assertNotNull($fila);
        $this->assertEquals(7, $fila->participaciones);
        $this->assertEquals(9.0, $fila->evaluacion_general);
        // Respuesta escapada (no debe contener el tag crudo).
        $this->assertEquals(e('<script>alert(1)</script>'), $fila->{'pregunta_' . $pregunta->id});
        $this->assertNotContains('<script>', (string) $fila->{'pregunta_' . $pregunta->id});
    }
}
