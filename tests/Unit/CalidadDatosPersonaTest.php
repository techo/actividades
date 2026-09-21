<?php

namespace Tests\Unit;

use App\Persona;
use App\Services\CalidadDatos\CalidadDatosPersona;
use App\Services\Documento\DocumentoService;
use Carbon\Carbon;
use Tests\TestCase;

/**
 * Tests del scoring de calidad de datos. Usa evaluarConAbreviacion() para no
 * resolver idPais => abreviación contra la base; las personas son instancias en
 * memoria (no persistidas). Extiende Tests\TestCase (no RefreshDatabase) para
 * tener el contenedor/Carbon disponibles, pero no ejecuta queries.
 */
class CalidadDatosPersonaTest extends TestCase
{
    private function calidad(): CalidadDatosPersona
    {
        $config = require __DIR__ . '/../../config/documentos.php';
        return new CalidadDatosPersona(new DocumentoService($config));
    }

    private function persona(array $attrs): Persona
    {
        return new Persona($attrs);
    }

    public function test_persona_completa_y_valida_es_ok()
    {
        $p = $this->persona([
            'nombres' => 'Juan', 'apellidoPaterno' => 'Pérez',
            'dni' => '12345678', 'tipo_documento' => 'dni_ar',
            'fechaNacimiento' => '1995-05-10',
        ]);
        $r = $this->calidad()->evaluarConAbreviacion($p, 'argentina');

        $this->assertSame('ok', $r['nivel']);
        $this->assertSame(100, $r['puntaje']);
        $this->assertEmpty($r['motivos']);
    }

    public function test_documento_invalido_es_critico()
    {
        $p = $this->persona([
            'nombres' => 'Juan', 'apellidoPaterno' => 'Pérez',
            'dni' => '123', 'tipo_documento' => 'dni_ar',
            'fechaNacimiento' => '1995-05-10',
        ]);
        $r = $this->calidad()->evaluarConAbreviacion($p, 'argentina');

        $this->assertSame('critico', $r['nivel']);
        $this->assertContains('backend.dq_doc_invalid', $r['motivos']);
    }

    public function test_documento_estricto_no_cae_a_pasaporte()
    {
        // Con tipo dni_ar, un alfanumérico que sería pasaporte válido en
        // auto-detect ahora es inválido (más seguro).
        $p = $this->persona([
            'nombres' => 'Juan', 'apellidoPaterno' => 'Pérez',
            'dni' => 'AB123456', 'tipo_documento' => 'dni_ar',
            'fechaNacimiento' => '1995-05-10',
        ]);
        $r = $this->calidad()->evaluarConAbreviacion($p, 'argentina');

        $this->assertContains('backend.dq_doc_invalid', $r['motivos']);
    }

    public function test_nombre_basura_es_sospechoso_y_critico()
    {
        $p = $this->persona([
            'nombres' => 'xxx', 'apellidoPaterno' => 'Pérez',
            'dni' => '12345678', 'tipo_documento' => 'dni_ar',
            'fechaNacimiento' => '1995-05-10',
        ]);
        $r = $this->calidad()->evaluarConAbreviacion($p, 'argentina');

        $this->assertContains('backend.dq_name_suspicious', $r['motivos']);
        $this->assertSame('critico', $r['nivel']); // un valor 'baja' fuerza crítico
    }

    public function test_minusculas_y_un_solo_token_no_penalizan()
    {
        // Evitamos falsos positivos: "juan" en minúscula, un solo token, es válido.
        $p = $this->persona([
            'nombres' => 'juan', 'apellidoPaterno' => 'perez',
            'dni' => '12345678', 'tipo_documento' => 'dni_ar',
            'fechaNacimiento' => '1995-05-10',
        ]);
        $r = $this->calidad()->evaluarConAbreviacion($p, 'argentina');

        $this->assertSame('ok', $r['nivel']);
    }

    public function test_fecha_implausible()
    {
        $p = $this->persona([
            'nombres' => 'Juan', 'apellidoPaterno' => 'Pérez',
            'dni' => '12345678', 'tipo_documento' => 'dni_ar',
            'fechaNacimiento' => '1850-01-01',
        ]);
        $r = $this->calidad()->evaluarConAbreviacion($p, 'argentina');

        $this->assertContains('backend.dq_birth_implausible', $r['motivos']);
    }

    public function test_datos_faltantes_bajan_puntaje_sin_ser_baja()
    {
        // Solo nombre: apellido/documento/fecha ausentes => 'sin_dato' (no 'baja').
        $p = $this->persona(['nombres' => 'Juan']);
        $r = $this->calidad()->evaluarConAbreviacion($p, 'argentina');

        $this->assertSame(20, $r['puntaje']); // solo el nombre (peso 20)
        $this->assertContains('backend.dq_lastname_missing', $r['motivos']);
        $this->assertContains('backend.dq_doc_missing', $r['motivos']);
        $this->assertContains('backend.dq_birth_missing', $r['motivos']);
        $this->assertSame('critico', $r['nivel']); // puntaje < 55
    }

    public function test_verificacion_reciente_marca_verificado()
    {
        $p = $this->persona([
            'nombres' => 'Juan', 'apellidoPaterno' => 'Pérez',
            'dni' => '12345678', 'tipo_documento' => 'dni_ar',
            'fechaNacimiento' => '1995-05-10',
            'datos_verificados_at' => Carbon::now()->subMonths(2),
        ]);
        $r = $this->calidad()->evaluarConAbreviacion($p, 'argentina');

        $this->assertTrue($r['verificado']);
    }

    public function test_verificacion_vencida_no_cuenta()
    {
        $p = $this->persona([
            'nombres' => 'Juan', 'apellidoPaterno' => 'Pérez',
            'dni' => '12345678', 'tipo_documento' => 'dni_ar',
            'fechaNacimiento' => '1995-05-10',
            'datos_verificados_at' => Carbon::now()->subMonths(CalidadDatosPersona::VIGENCIA_MESES + 1),
        ]);
        $r = $this->calidad()->evaluarConAbreviacion($p, 'argentina');

        $this->assertFalse($r['verificado']);
    }
}
