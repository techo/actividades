<?php

namespace Tests\Feature;

use App\Search\SortSanitizer;
use Tests\TestCase;

/**
 * Regresión: el datatable puede pedir orden por una columna de DISPLAY que no
 * existe físicamente en la tabla del listado (p.ej. "nombre" en Integrantes,
 * "comunidades" en el export de actividades). Antes eso pasaba el filtro
 * anti-inyección de SortSanitizer y llegaba a orderByRaw() como
 * "Unknown column ... in 'order clause'" → 500 en producción.
 *
 * Con el argumento $table, SortSanitizer descarta la columna inexistente y cae
 * al fallback seguro. Necesita el schema real (Schema::hasColumn) → Feature.
 */
class SortSanitizerSchemaTest extends TestCase
{
    /** @test */
    public function ordenar_por_columna_inexistente_de_la_tabla_usa_fallback()
    {
        // 'nombre' no es una columna de Integrantes (el nombre vive en Persona).
        $this->assertEquals(
            'created_at desc',
            SortSanitizer::sanitize('nombre asc', 'created_at desc', 'Integrantes')
        );
    }

    /** @test */
    public function ordenar_por_columna_existente_de_la_tabla_se_respeta()
    {
        // 'hitos' sí es una columna real de Integrantes.
        $this->assertEquals(
            'hitos asc',
            SortSanitizer::sanitize('hitos asc', 'created_at desc', 'Integrantes')
        );
    }

    /** @test */
    public function sin_tabla_mantiene_el_comportamiento_anterior()
    {
        // Backward-compat: sin $table no se valida existencia (solo anti-inyección).
        $this->assertEquals(
            'nombre asc',
            SortSanitizer::sanitize('nombre asc')
        );
    }
}
