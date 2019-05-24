<?php

namespace Tests\Feature\ajax;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ajaxPaisesTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function ver_listado_de_paises()
    {
        factory('App\Pais')->create();

        $response = $this->get('/ajax/paises/');
        $response
            ->assertStatus(200)
            ->assertJsonStructure(
                [
                    '*' => [
                        'id',
                        'nombre'
                    ]
                ]
            );
    }

    /** @test */
    public function ver_listado_de_provincias_por_pais()
    {
        $pais = factory('App\Pais')->create();
        $url = '/ajax/paises/' . $pais->id . '/provincias';
        $response = $this->get($url);
        $response
            ->assertStatus(200)
            ->assertJsonStructure(
                [
                    '*' => [
                        'id',
                        'provincia',
                        'id_pais',
                    ]
                ]
            );
    }
    public function test_ver_listado_de_localidades_por_provincia_y_por_pais()
    {
        $provincia = factory('App\Provincia')->create();

        $pais = $provincia->pais;

        $this->get('/ajax/paises/' . $pais->id . '/provincias/'. $provincia->id .'/localidades')
            ->assertStatus(200)
            ->assertJsonStructure(
                [
                    '*' => [
                        'id',
                        'id_provincia',
                        'localidad',
                    ]
                ]
            );
    }
}
