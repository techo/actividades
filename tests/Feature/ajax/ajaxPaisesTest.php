<?php

namespace Tests\Feature\ajax;

use App\Pais;
use Tests\TestCase;

class ajaxPaisesTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function test_ver_listado_de_paises()
    {
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
        $pais = Pais::first();
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
        $pais = Pais::where('nombre', 'Argentina')->first();
        //dd($pais);
        $provincia = $pais->provincias->first();

        $url = '/ajax/paises/' . $pais->id . '/provincias/'. $provincia->id .'/localidades';
        //dd($url);
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
