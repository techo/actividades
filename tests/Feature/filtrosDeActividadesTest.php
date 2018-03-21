<?php

namespace Tests\Feature;

use Tests\TestCase;

class filtrosDeActividadesTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function test_tipos_de_actividad_sin_parametros()
    {
        $params = [];

        $response = $this->post('/ajax/actividades/tipos', $params);
        $response
            ->assertStatus(200)
            ->assertJsonStructure(
                [
                    [
                    "idTipo",
                    "nombre"
                    ]
                ]
            );
    }

    public function test_provincias_y_localidades_sin_parametros()
    {
        $params = [];

        $response = $this->post('/ajax/actividades/provincias/', $params);
        $response
            ->assertStatus(200)
            ->assertJsonStructure(
                [ '*'=>
                    [
                        "id_provincia",
                        "provincia",
                        "localidades" =>  []
                    ]
                ]
            );
    }

    public function test_actividades_sin_parametros()
    {
        $params = [];
        $response = $this->post('/ajax/actividades', $params);
        $response
            ->assertStatus(200)
            ->assertJsonStructure(
                [
                    "current_page",
                    "data"=> [],
                    "first_page_url",
                    "from",
                    "last_page",
                    "last_page_url",
                    "next_page_url",
                    "path",
                    "per_page",
                    "prev_page_url",
                    "to",
                    "total"
                ]
            );
    }
}
