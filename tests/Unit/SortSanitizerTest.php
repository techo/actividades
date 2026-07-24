<?php

namespace Tests\Unit;

use App\Search\SortSanitizer;
use PHPUnit\Framework\TestCase;

/**
 * Regresión de C-3: inyección SQL vía ORDER BY ($request->sort → orderByRaw).
 * SortSanitizer debe dejar pasar solo "columna [asc|desc]" y descartar todo lo demás.
 */
class SortSanitizerTest extends TestCase
{
    /** @test */
    public function acepta_columna_y_direccion_validas()
    {
        $this->assertEquals('nombres asc', SortSanitizer::sanitize('nombres asc'));
        $this->assertEquals('nombres desc', SortSanitizer::sanitize('nombres desc'));
        // La dirección se normaliza a minúsculas.
        $this->assertEquals('created_at desc', SortSanitizer::sanitize('created_at DESC'));
    }

    /** @test */
    public function normaliza_direccion_a_minusculas_y_default_asc()
    {
        $this->assertEquals('nombres desc', SortSanitizer::sanitize('nombres DESC'));
        $this->assertEquals('nombres asc', SortSanitizer::sanitize('nombres'));
    }

    /** @test */
    public function acepta_tabla_punto_columna()
    {
        $this->assertEquals('Actividad.fechaInicio desc', SortSanitizer::sanitize('Actividad.fechaInicio desc'));
    }

    /** @test */
    public function descarta_payloads_de_inyeccion_y_usa_fallback()
    {
        $fallback = 'idPersona desc';

        $payloads = [
            "(select case when (ascii(substring((select password from Persona limit 1),1,1))>77) then idPersona else mail end)",
            "nombres; drop table Persona",
            "nombres,mail",                       // múltiples columnas / coma
            "if(1=1,sleep(5),0)",
            "nombres asc, (select 1)",
            "nombres/**/asc",
            "`nombres`",                          // backtick
            "nombres asc desc",                   // más de 2 tokens
            "nombres' or '1'='1",
            "1=1",
        ];

        foreach ($payloads as $payload) {
            $this->assertEquals(
                $fallback,
                SortSanitizer::sanitize($payload, $fallback),
                "El payload no fue descartado: {$payload}"
            );
        }
    }

    /** @test */
    public function direccion_invalida_usa_fallback()
    {
        $this->assertEquals('created_at desc', SortSanitizer::sanitize('nombres ascending', 'created_at desc'));
    }

    /** @test */
    public function valores_vacios_o_nulos_usan_fallback()
    {
        $this->assertEquals('created_at desc', SortSanitizer::sanitize(null, 'created_at desc'));
        $this->assertEquals('created_at desc', SortSanitizer::sanitize('', 'created_at desc'));
        $this->assertEquals('created_at desc', SortSanitizer::sanitize('   ', 'created_at desc'));
    }
}
