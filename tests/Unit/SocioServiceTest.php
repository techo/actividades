<?php

namespace Tests\Unit;

use App\Persona;
use App\Services\Salesforce\SalesforceClient;
use App\Services\Salesforce\SocioService;
use Mockery;
use Tests\TestCase;

/**
 * SocioService: ¿la persona es socio/donante de TECHO? (Salesforce Sustainer).
 *
 * No usa base de datos: las Persona se arman en memoria y el SalesforceClient
 * se mockea. Cubre los gates (flag/país/DNI), el parseo de la respuesta SOQL,
 * el fail-closed ante error y la memoización in-request.
 */
class SocioServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config([
            'services.salesforce.enabled'       => true,
            'services.salesforce.socio_pais_id' => 13,
            'services.salesforce.cache_ttl'     => 1,
        ]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function persona($attrs = []): Persona
    {
        $p = new Persona();
        $p->idPersona = $attrs['idPersona'] ?? 1;
        $p->idPais    = $attrs['idPais'] ?? 13;
        $p->dni       = $attrs['dni'] ?? '24134990';
        return $p;
    }

    /** SalesforceClient que devuelve la respuesta SOQL dada; ->query se espera $times veces. */
    private function clientQueRetorna(array $soqlResponse, $times = 1): SalesforceClient
    {
        $mock = Mockery::mock(SalesforceClient::class);
        $mock->shouldReceive('query')->times($times)->andReturn($soqlResponse);
        return $mock;
    }

    /** @test */
    public function con_feature_flag_apagado_no_llama_a_salesforce_y_es_false()
    {
        config(['services.salesforce.enabled' => false]);
        $client = Mockery::mock(SalesforceClient::class);
        $client->shouldNotReceive('query');

        $this->assertFalse((new SocioService($client))->esSocio($this->persona()));
    }

    /** @test */
    public function persona_de_otro_pais_no_llama_a_salesforce_y_es_false()
    {
        $client = Mockery::mock(SalesforceClient::class);
        $client->shouldNotReceive('query');

        $service = new SocioService($client);
        $this->assertFalse($service->esSocio($this->persona(['idPais' => 5])));
    }

    /** @test */
    public function persona_sin_dni_no_llama_a_salesforce_y_es_false()
    {
        $client = Mockery::mock(SalesforceClient::class);
        $client->shouldNotReceive('query');

        $service = new SocioService($client);
        $this->assertFalse($service->esSocio($this->persona(['dni' => ''])));
    }

    /** @test */
    public function sustainer_true_es_socio()
    {
        $client = $this->clientQueRetorna([
            'totalSize' => 1,
            'records'   => [['npsp__Sustainer__c' => true]],
        ]);

        $this->assertTrue((new SocioService($client))->esSocio($this->persona()));
    }

    /** @test */
    public function dni_no_encontrado_no_es_socio()
    {
        $client = $this->clientQueRetorna(['totalSize' => 0, 'records' => []]);

        $this->assertFalse((new SocioService($client))->esSocio($this->persona()));
    }

    /** @test */
    public function contacto_existente_pero_no_sustainer_no_es_socio()
    {
        $client = $this->clientQueRetorna([
            'totalSize' => 1,
            'records'   => [['npsp__Sustainer__c' => false]],
        ]);

        $this->assertFalse((new SocioService($client))->esSocio($this->persona()));
    }

    /** @test */
    public function fail_closed_ante_error_de_salesforce_es_false()
    {
        $client = Mockery::mock(SalesforceClient::class);
        $client->shouldReceive('query')->andThrow(new \RuntimeException('Salesforce caído'));

        $this->assertFalse((new SocioService($client))->esSocio($this->persona()));
    }

    /** @test */
    public function memoiza_por_persona_en_el_mismo_request()
    {
        // ->query se espera UNA sola vez aunque se consulte dos veces la misma persona.
        $client  = $this->clientQueRetorna(
            ['totalSize' => 1, 'records' => [['npsp__Sustainer__c' => true]]],
            1
        );
        $service = new SocioService($client);
        $persona = $this->persona();

        $this->assertTrue($service->esSocio($persona));
        $this->assertTrue($service->esSocio($persona));
    }
}
