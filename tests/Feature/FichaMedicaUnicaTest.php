<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Reclamo #31 (oct-2026): una voluntaria aparecía triplicada en todos los listados de
 * inscripciones porque tenía tres fichas médicas (alta sin lock) y el listado hace
 * LEFT JOIN ficha_medicas.
 */
class FichaMedicaUnicaTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function guardar_la_ficha_dos_veces_no_crea_otra()
    {
        $persona = factory('App\Persona')->create();

        $this->actingAs($persona)->postJson('/ajax/fichaMedica', ['confirma_datos' => 1, 'alergias' => 'polen'])->assertStatus(200);
        $this->actingAs($persona)->postJson('/ajax/fichaMedica', ['confirma_datos' => 1, 'alergias' => 'ninguna'])->assertStatus(200);

        $this->assertEquals(1, DB::table('ficha_medicas')->where('idPersona', $persona->idPersona)->count());
        $this->assertEquals('ninguna', DB::table('ficha_medicas')->where('idPersona', $persona->idPersona)->value('alergias'));
    }

    /** @test */
    public function la_base_rechaza_una_segunda_ficha_para_la_misma_persona()
    {
        // La deduplicación de la migración se verificó a mano contra una base con
        // duplicadas: probarla acá exige DDL (dropUnique), que en MySQL hace commit
        // implícito y rompe el aislamiento de RefreshDatabase.
        $persona = factory('App\Persona')->create();
        DB::table('ficha_medicas')->insert(['idPersona' => $persona->idPersona]);

        $this->expectException(\Illuminate\Database\QueryException::class);
        DB::table('ficha_medicas')->insert(['idPersona' => $persona->idPersona]);
    }
}
