<?php

namespace Tests\Feature\ajax;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Faker\Generator as Faker;

class ajaxPaisesTest extends TestCase
{
    use DatabaseTransactions;

    public function test_ver_listado_de_paises()
    {
        factory(\App\Pais::class, 4)->create();

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

    public function test_ver_listado_de_provincias_por_pais()
    {
        $pais = factory(\App\Pais::class)->create();
        factory(\App\Provincia::class, 4)->create([ 'id_pais' => $pais->id ]);

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
        $provincia = factory(\App\Provincia::class)->create();
        factory(\App\Localidad::class, 4)->create([ 'id_provincia' => $provincia->id ]);

        $url = '/ajax/paises/' . $provincia->id_pais . '/provincias/'. $provincia->id .'/localidades';

        $response = $this->get($url);
        $response
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
