<?php

namespace Tests\Feature;

use App\EvaluacionPersonaRespuesta;
use App\Inscripcion;
use App\ListadoColumna;
use App\ListadoColumnaValor;
use App\Search\InscripcionesSearch;
use App\Services\Listados\EnriquecedorFilas;
use App\Services\Listados\ListadoQuery;
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

    // ── Fase 2: filtros genéricos + recuento ───────────────────────────────

    /** Crea una actividad con N inscriptos con género controlado. Devuelve [actividad, inscripciones]. */
    private function actividadConGeneros($creador, array $generos): array
    {
        $actividad = app(ActividadFactory::class)->creadaPor($creador)->agregarPuntoConInscriptos(0)->create();
        $punto = $actividad->puntosEncuentro->first();

        $inscripciones = [];
        foreach ($generos as $genero) {
            $persona = factory('App\Persona')->create(['genero' => $genero]);
            $inscripciones[] = factory('App\Inscripcion')->create([
                'idActividad' => $actividad->idActividad,
                'idPuntoEncuentro' => $punto->idPuntoEncuentro,
                'idPersona' => $persona->idPersona,
            ]);
        }

        return [$actividad, $inscripciones];
    }

    private function filtros($idActividad, array $condiciones): array
    {
        return array_merge(
            ['idActividad' => $idActividad],
            $condiciones,
            ['__filterable' => (new ListadoQuery())->metaFiltrable('inscripciones', $idActividad)]
        );
    }

    /** @test */
    public function el_filtro_generico_filtra_por_un_campo_base()
    {
        $this->withoutExceptionHandling();
        $this->seed('PermisosSeeder');

        $admin = $this->admin();
        [$actividad] = $this->actividadConGeneros($admin, ['M', 'F', 'F']);

        $filas = InscripcionesSearch::query(
            $this->filtros($actividad->idActividad, ['genero' => ['condicion' => '=', 'valor' => 'F']])
        )->get();

        $this->assertCount(2, $filas);
        $this->assertTrue($filas->every(function ($f) { return $f->genero === 'F'; }));
    }

    /** @test */
    public function el_filtro_generico_filtra_por_una_columna_de_seguimiento()
    {
        $this->withoutExceptionHandling();
        $this->seed('PermisosSeeder');

        $admin = $this->admin();
        [$actividad, $inscripciones] = $this->actividadConGeneros($admin, ['M', 'F']);

        $columna = ListadoColumna::create([
            'list_key' => 'inscripciones', 'context_id' => $actividad->idActividad,
            'nombre' => 'Estado contacto', 'tipo' => 'estado',
            'opciones' => ['Contactado', 'En espera'], 'orden' => 1, 'created_by' => $admin->idPersona,
        ]);
        // Solo el primer inscripto queda "Contactado".
        ListadoColumnaValor::create([
            'columna_id' => $columna->id, 'record_id' => $inscripciones[0]->idInscripcion,
            'valor' => 'Contactado', 'updated_by' => $admin->idPersona,
        ]);

        $filas = InscripcionesSearch::query(
            $this->filtros($actividad->idActividad, ['custom_' . $columna->id => ['condicion' => '=', 'valor' => 'Contactado']])
        )->get();

        $this->assertCount(1, $filas);
        $this->assertEquals($inscripciones[0]->idInscripcion, $filas->first()->id);
    }

    /** @test */
    public function el_filtro_generico_casilla_distingue_marcada_de_sin_valor()
    {
        $this->withoutExceptionHandling();
        $this->seed('PermisosSeeder');

        $admin = $this->admin();
        [$actividad, $inscripciones] = $this->actividadConGeneros($admin, ['M', 'F']);

        $columna = ListadoColumna::create([
            'list_key' => 'inscripciones', 'context_id' => $actividad->idActividad,
            'nombre' => '¿Firmó?', 'tipo' => 'casilla', 'orden' => 1, 'created_by' => $admin->idPersona,
        ]);
        ListadoColumnaValor::create([
            'columna_id' => $columna->id, 'record_id' => $inscripciones[1]->idInscripcion,
            'valor' => '1', 'updated_by' => $admin->idPersona,
        ]);

        $marcadas = InscripcionesSearch::query(
            $this->filtros($actividad->idActividad, ['custom_' . $columna->id => ['condicion' => '=', 'valor' => 1]])
        )->get();
        $this->assertCount(1, $marcadas);
        $this->assertEquals($inscripciones[1]->idInscripcion, $marcadas->first()->id);

        $sinMarcar = InscripcionesSearch::query(
            $this->filtros($actividad->idActividad, ['custom_' . $columna->id => ['condicion' => '=', 'valor' => 0]])
        )->get();
        $this->assertCount(1, $sinMarcar);
        $this->assertEquals($inscripciones[0]->idInscripcion, $sinMarcar->first()->id);
    }

    /** @test */
    public function el_endpoint_count_devuelve_total_y_preview()
    {
        $this->withoutExceptionHandling();
        $this->seed('PermisosSeeder');

        $admin = $this->admin();
        [$actividad] = $this->actividadConGeneros($admin, ['M', 'F', 'F']);
        $url = '/admin/ajax/listados/inscripciones/' . $actividad->idActividad . '/count';

        // Total sin filtros = 3.
        $this->actingAs($admin)->getJson($url)->assertStatus(200)->assertJson(['total' => 3]);

        // Preview de una condición género = F → 2 coincidencias.
        $this->actingAs($admin)
            ->getJson($url . '?preview=' . urlencode(json_encode(['campo' => 'genero', 'condicion' => '=', 'valor' => 'F'])))
            ->assertStatus(200)
            ->assertJson(['total' => 3, 'preview' => 2]);
    }

    /** @test */
    public function el_endpoint_facets_cuenta_por_grupo_en_un_campo_base()
    {
        $this->withoutExceptionHandling();
        $this->seed('PermisosSeeder');

        $admin = $this->admin();
        [$actividad] = $this->actividadConGeneros($admin, ['M', 'F', 'F']);

        $data = $this->actingAs($admin)
            ->getJson('/admin/ajax/listados/inscripciones/' . $actividad->idActividad . '/facets?group_by=genero')
            ->assertStatus(200)
            ->json();

        $this->assertEquals('genero', $data['field']);
        $buckets = collect($data['buckets'])->keyBy('valor');
        $this->assertEquals(2, $buckets['F']['total']);
        $this->assertEquals(1, $buckets['M']['total']);
    }

    /** @test */
    public function el_endpoint_facets_cuenta_por_columna_de_seguimiento_con_sin_valor()
    {
        $this->withoutExceptionHandling();
        $this->seed('PermisosSeeder');

        $admin = $this->admin();
        [$actividad, $inscripciones] = $this->actividadConGeneros($admin, ['M', 'F', 'F']);

        $columna = ListadoColumna::create([
            'list_key' => 'inscripciones', 'context_id' => $actividad->idActividad,
            'nombre' => 'Canal', 'tipo' => 'estado',
            'opciones' => ['WhatsApp', 'Email'], 'orden' => 1, 'created_by' => $admin->idPersona,
        ]);
        ListadoColumnaValor::create(['columna_id' => $columna->id, 'record_id' => $inscripciones[0]->idInscripcion, 'valor' => 'WhatsApp', 'updated_by' => $admin->idPersona]);
        ListadoColumnaValor::create(['columna_id' => $columna->id, 'record_id' => $inscripciones[1]->idInscripcion, 'valor' => 'WhatsApp', 'updated_by' => $admin->idPersona]);
        // El tercero queda sin valor.

        $data = $this->actingAs($admin)
            ->getJson('/admin/ajax/listados/inscripciones/' . $actividad->idActividad . '/facets?group_by=custom_' . $columna->id)
            ->assertStatus(200)
            ->json();

        $buckets = collect($data['buckets'])->keyBy('valor');
        $this->assertEquals(2, $buckets['WhatsApp']['total']);
        $this->assertEquals(1, $data['sin_valor']);
    }

    /** @test */
    public function los_facets_respetan_los_filtros_activos()
    {
        $this->withoutExceptionHandling();
        $this->seed('PermisosSeeder');

        $admin = $this->admin();
        [$actividad] = $this->actividadConGeneros($admin, ['M', 'F', 'F']);

        // Con un filtro género = F, el facet por estado_persona solo cuenta esas 2.
        $data = $this->actingAs($admin)
            ->getJson('/admin/ajax/listados/inscripciones/' . $actividad->idActividad
                . '/facets?group_by=genero&condiciones[]=' . urlencode(json_encode(['campo' => 'genero', 'condicion' => '=', 'valor' => 'F'])))
            ->assertStatus(200)
            ->json();

        $buckets = collect($data['buckets'])->keyBy('valor');
        $this->assertEquals(2, $buckets['F']['total']);
        $this->assertFalse($buckets->has('M'), 'M no debería aparecer con el filtro género = F');
    }

    // ── Fase 4: vistas guardadas ───────────────────────────────────────────

    /** @test */
    public function las_vistas_combinan_predefinidas_y_propias()
    {
        $this->withoutExceptionHandling();
        $this->seed('PermisosSeeder');

        $admin = $this->admin();
        [$actividad] = $this->actividadConGeneros($admin, ['M']);
        $url = '/admin/ajax/listados/inscripciones/' . $actividad->idActividad . '/vistas';

        // Una vista propia.
        $this->actingAs($admin)->postJson($url, [
            'nombre' => 'Mujeres',
            'color' => '#e91e63',
            'config' => ['filtros' => [['campo' => 'genero', 'condicion' => '=', 'valor' => 'F']], 'group_by' => 'oficina'],
        ])->assertStatus(200);

        $data = $this->actingAs($admin)->getJson($url)->assertStatus(200)->json();

        // Predefinida "Todos" presente y read-only.
        $this->assertNotEmpty($data['predefinidas']);
        $this->assertTrue($data['predefinidas'][0]['es_predefinida']);
        // Propia guardada.
        $this->assertCount(1, $data['propias']);
        $this->assertEquals('Mujeres', $data['propias'][0]['nombre']);
        $this->assertEquals('genero', $data['propias'][0]['config']['filtros'][0]['campo']);
        $this->assertEquals('oficina', $data['propias'][0]['config']['group_by']);
    }

    /** @test */
    public function una_vista_no_puede_referenciar_un_campo_no_consultable()
    {
        $this->seed('PermisosSeeder');

        $admin = $this->admin();
        [$actividad] = $this->actividadConGeneros($admin, ['M']);
        $url = '/admin/ajax/listados/inscripciones/' . $actividad->idActividad . '/vistas';

        // Filtro por un agregado post-paginación → rechazado.
        $this->actingAs($admin)->postJson($url, [
            'nombre' => 'Rota', 'config' => ['filtros' => [['campo' => 'participaciones', 'condicion' => '>', 'valor' => 3]]],
        ])->assertStatus(422);

        // group_by por un campo no agrupable (dni) → rechazado.
        $this->actingAs($admin)->postJson($url, [
            'nombre' => 'Rota2', 'config' => ['filtros' => [], 'group_by' => 'dni'],
        ])->assertStatus(422);

        $this->assertDatabaseMissing('listado_vistas', ['nombre' => 'Rota']);
    }

    /** @test */
    public function no_se_puede_eliminar_una_vista_de_otro_usuario()
    {
        $this->seed('PermisosSeeder');

        $admin = $this->admin();
        $otro = $this->admin();
        [$actividad] = $this->actividadConGeneros($admin, ['M']);
        $url = '/admin/ajax/listados/inscripciones/' . $actividad->idActividad . '/vistas';

        $vista = $this->actingAs($admin)->postJson($url, [
            'nombre' => 'Mía', 'config' => ['filtros' => [], 'group_by' => null],
        ])->json('vista');

        $this->actingAs($otro)->deleteJson($url . '/' . $vista['id'])->assertStatus(404);
        $this->assertDatabaseHas('listado_vistas', ['id' => $vista['id']]);
    }

    // ── Fase 5: Suscriptos sobre el mismo módulo genérico ──────────────────

    /** @test */
    public function el_modulo_generico_funciona_para_suscriptos()
    {
        $this->withoutExceptionHandling();
        $this->seed('PermisosSeeder');

        $admin = factory('App\Persona')->create(['idPaisPermitido' => 13]);
        $admin->assignRole('admin');

        $campaign = \App\Campaign::create(['nombre' => 'Colecta 2026', 'estado' => 'activa', 'activa' => 1, 'pais_id' => 13]);

        $sub = function ($nombre, $canal, $conv) use ($campaign) {
            return \App\Suscribe::create([
                'nombre' => $nombre, 'apellido' => 'Test', 'mail' => strtolower($nombre) . '@x.com',
                'idPais' => 13, 'campaign_id' => $campaign->id, 'canal_contacto' => $canal, 'convertido' => $conv,
            ]);
        };
        $ana = $sub('Ana', 'WhatsApp', 1);
        $sub('Luis', 'Email', 1);
        $sub('Eva', 'WhatsApp', 0);

        $url = '/admin/ajax/listados/suscriptos/' . $campaign->id;

        // config: grupos + campos filtrables/agrupables propios de suscriptos.
        $config = $this->actingAs($admin)->getJson($url . '/config')->assertStatus(200)->json();
        $this->assertTrue(collect($config['grupos'])->keyBy('key')->has('datos_generales'));
        $this->assertTrue(collect($config['filtrables'])->pluck('key')->contains('canal_contacto'));
        $this->assertTrue(collect($config['agrupables'])->pluck('key')->contains('convertido'));

        // count total + preview de convertido = 1.
        $this->actingAs($admin)->getJson($url . '/count')->assertStatus(200)->assertJson(['total' => 3]);
        $this->actingAs($admin)
            ->getJson($url . '/count?preview=' . urlencode(json_encode(['campo' => 'convertido', 'condicion' => '=', 'valor' => 1])))
            ->assertJson(['preview' => 2]);

        // facets por canal de contacto.
        $facets = $this->actingAs($admin)->getJson($url . '/facets?group_by=canal_contacto')->assertStatus(200)->json();
        $buckets = collect($facets['buckets'])->keyBy('valor');
        $this->assertEquals(2, $buckets['WhatsApp']['total']);
        $this->assertEquals(1, $buckets['Email']['total']);

        // columna de seguimiento + valor + filtro genérico (misma lógica que inscripciones).
        $columna = $this->actingAs($admin)->postJson($url . '/columnas', [
            'nombre' => 'Seguimiento', 'tipo' => 'estado', 'opciones' => ['Llamar', 'Listo'],
        ])->assertStatus(200)->json('columna');
        $this->actingAs($admin)->putJson($url . '/columnas/' . $columna['id'] . '/valores/' . $ana->id, ['valor' => 'Llamar'])->assertStatus(200);

        $this->actingAs($admin);
        $filtros = array_merge(
            ['campaign_id' => $campaign->id, 'custom_' . $columna['id'] => ['condicion' => '=', 'valor' => 'Llamar']],
            ['__filterable' => (new ListadoQuery())->metaFiltrable('suscriptos', $campaign->id)]
        );
        $filas = \App\Search\SuscriptosSearch::query($filtros)->get();
        $this->assertCount(1, $filas);
        $this->assertEquals($ana->id, $filas->first()->id);

        // vistas predefinidas propias del listado: una filtra por convertido
        // (aserción por config, independiente del locale del nombre).
        $vistas = $this->actingAs($admin)->getJson($url . '/vistas')->assertStatus(200)->json();
        $this->assertGreaterThanOrEqual(2, count($vistas['predefinidas']));
        $filtraConvertido = collect($vistas['predefinidas'])->contains(function ($vista) {
            return collect($vista['config']['filtros'] ?? [])->contains('campo', 'convertido');
        });
        $this->assertTrue($filtraConvertido, 'Debe existir una vista predefinida que filtre por convertido');
    }

    /** @test */
    public function un_pais_distinto_no_puede_ver_suscriptos_de_la_campana()
    {
        $this->seed('PermisosSeeder');

        $admin = factory('App\Persona')->create(['idPaisPermitido' => 99]);
        $admin->assignRole('admin');
        $campaign = \App\Campaign::create(['nombre' => 'Otra', 'estado' => 'activa', 'activa' => 1, 'pais_id' => 13]);

        $this->actingAs($admin)
            ->getJson('/admin/ajax/listados/suscriptos/' . $campaign->id . '/config')
            ->assertStatus(403);
    }

    /** @test */
    public function los_campos_filtrables_vienen_agrupados_como_las_columnas()
    {
        $this->withoutExceptionHandling();
        $this->seed('PermisosSeeder');

        $admin = $this->admin();
        [$actividad] = $this->actividadConGeneros($admin, ['M']);

        $data = $this->actingAs($admin)
            ->getJson('/admin/ajax/listados/inscripciones/' . $actividad->idActividad . '/config')
            ->assertStatus(200)->json();

        $filtrables = collect($data['filtrables']);
        // Cada filtrable trae su grupo (misma categorización que el panel de columnas).
        $this->assertTrue($filtrables->every(function ($f) { return !empty($f['grupo']); }));
        // Datos generales y Ficha médica presentes como grupos filtrables.
        $porKey = $filtrables->keyBy('key');
        $this->assertEquals('datos_generales', $porKey['dni']['grupo']);
        $this->assertTrue($porKey->has('grupo_sanguinieo'), 'Ficha médica debe ser filtrable');
        $this->assertEquals('ficha_medica', $porKey['grupo_sanguinieo']['grupo']);
    }

    /** @test */
    public function el_index_de_inscripciones_aplica_los_filtros_avanzados()
    {
        $this->withoutExceptionHandling();
        $this->seed('PermisosSeeder');

        $admin = $this->admin();
        [$actividad] = $this->actividadConGeneros($admin, ['M', 'F', 'F']);

        $cond = json_encode(['campo' => 'genero', 'condicion' => '=', 'valor' => 'F']);
        $data = $this->actingAs($admin)
            ->getJson('/admin/ajax/actividades/' . $actividad->idActividad . '/inscripciones?condiciones[]=' . urlencode($cond))
            ->assertStatus(200)
            ->json();

        // El index (refactorizado a ListadoQuery) devuelve solo las 2 coincidencias.
        $this->assertEquals(2, $data['total']);
    }

    /** @test */
    public function el_filtro_generico_ignora_campos_no_registrados()
    {
        $this->withoutExceptionHandling();
        $this->seed('PermisosSeeder');

        $admin = $this->admin();
        [$actividad] = $this->actividadConGeneros($admin, ['M', 'F']);

        // Un campo arbitrario (no está en filterableFields ni tiene clase de filtro)
        // se ignora en silencio: no filtra ni rompe el query (gate anti-inyección).
        $filas = InscripcionesSearch::query(
            $this->filtros($actividad->idActividad, [
                'campo_inventado' => ['condicion' => '=', 'valor' => 'x'],
                'custom_999999' => ['condicion' => '=', 'valor' => 'y'],
            ])
        )->get();

        $this->assertCount(2, $filas);
    }

    /** @test */
    public function filterable_fields_excluye_los_agregados_post_paginacion()
    {
        $this->seed('PermisosSeeder');

        $admin = $this->admin();
        [$actividad] = $this->actividadConGeneros($admin, ['M']);

        $meta = (new ListadoQuery())->metaFiltrable('inscripciones', $actividad->idActividad);

        foreach (['participaciones', 'nivel', 'evaluacion_general'] as $key) {
            $this->assertArrayNotHasKey($key, $meta, "$key no debe ser filtrable (es agregado post-paginación)");
        }
        // Campos base sí presentes.
        $this->assertArrayHasKey('genero', $meta);
        $this->assertArrayHasKey('dni', $meta);
    }
}
