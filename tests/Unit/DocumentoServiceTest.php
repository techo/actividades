<?php

namespace Tests\Unit;

use App\Services\Documento\DocumentoService;
use PHPUnit\Framework\TestCase;

/**
 * Tests puros (sin BD) del validador/normalizador de documentos. Se usa
 * validarPorAbreviacion() para evitar resolver idPais => abreviación contra el
 * modelo Pais (eso sí toca base). La config se inyecta directo desde el archivo.
 */
class DocumentoServiceTest extends TestCase
{
    private function service(): DocumentoService
    {
        $config = require __DIR__ . '/../../config/documentos.php';
        return new DocumentoService($config);
    }

    // -- Argentina (DNI, sin verificador) -------------------------------------

    public function test_dni_argentino_valido()
    {
        $r = $this->service()->validarPorAbreviacion('argentina', '12.345.678');
        $this->assertTrue($r['valido']);
        $this->assertSame('dni_ar', $r['tipo']);
        $this->assertSame('12345678', $r['normalizado']); // normaliza sin puntos
    }

    public function test_dni_argentino_corto_invalido()
    {
        $this->assertFalse($this->service()->validarPorAbreviacion('argentina', '123')['valido']);
    }

    public function test_extranjero_en_argentina_con_pasaporte()
    {
        $r = $this->service()->validarPorAbreviacion('argentina', 'AB123456');
        $this->assertTrue($r['valido']);
        $this->assertSame('pasaporte', $r['tipo']);
    }

    // -- Brasil (CPF con doble verificador mód. 11) ---------------------------

    public function test_cpf_valido()
    {
        // 529.982.247-25 es un CPF con verificador correcto (número de test público).
        $r = $this->service()->validarPorAbreviacion('brasil', '529.982.247-25');
        $this->assertTrue($r['valido']);
        $this->assertSame('cpf', $r['tipo']);
        $this->assertSame('52998224725', $r['normalizado']);
    }

    public function test_cpf_con_verificador_incorrecto_es_invalido()
    {
        // Último dígito cambiado: falla el verificador. Y al ser todo numérico,
        // tampoco pasa como pasaporte (que exige una letra) => rechazado.
        $this->assertFalse($this->service()->validarPorAbreviacion('brasil', '52998224724')['valido']);
    }

    public function test_cpf_de_digitos_repetidos_es_invalido()
    {
        $this->assertFalse($this->service()->validarPorAbreviacion('brasil', '11111111111')['valido']);
    }

    // -- Chile (RUT con verificador mód. 11) ----------------------------------

    public function test_rut_valido()
    {
        // 12.345.678-5: DV calculado = 5.
        $r = $this->service()->validarPorAbreviacion('chile', '12.345.678-5');
        $this->assertTrue($r['valido']);
        $this->assertSame('rut', $r['tipo']);
        $this->assertSame('123456785', $r['normalizado']);
    }

    public function test_rut_con_verificador_incorrecto_es_invalido()
    {
        $this->assertFalse($this->service()->validarPorAbreviacion('chile', '12.345.678-4')['valido']);
    }

    // -- Casos generales ------------------------------------------------------

    public function test_vacio_es_valido_y_no_rompe()
    {
        $r = $this->service()->validarPorAbreviacion('argentina', '');
        $this->assertTrue($r['valido']); // la presencia la maneja required/nullable
        $this->assertSame('', $r['normalizado']);
    }

    public function test_pais_no_configurado_usa_default_permisivo()
    {
        // 'default' = [generico, pasaporte]; un documento numérico razonable pasa.
        $this->assertTrue($this->service()->validarPorAbreviacion(null, '12345678')['valido']);
        // pero basura corta no.
        $this->assertFalse($this->service()->validarPorAbreviacion(null, 'x1')['valido']);
    }

    public function test_normalizar_devuelve_canonico()
    {
        $this->assertSame('52998224725', $this->service()->validarPorAbreviacion('brasil', '529.982.247-25')['normalizado']);
    }
}
