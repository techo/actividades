<?php

namespace Tests\Feature\ajax;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ajaxActividadesTest extends TestCase
{
    use RefreshDatabase;
    
    /** @test */
    public function tipos_de_actividad_sin_parametros()
    {
        $actividad = factory('App\Actividad')->create();

        $params = [];

        $response = $this->get('/ajax/actividades/tipos', $params);
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

    /** @test */
    public function provincias_y_localidades_sin_parametros()
    {
        $params = [];

        $response = $this->json('GET', '/ajax/actividades/provincias/', $params);

        $response
            ->assertStatus(200)
            ->assertJsonStructure(
                ['*' =>
                    [
                        "id_provincia",
                        "provincia",
                        "localidades" => []
                    ]
                ]
            );
    }

    /** @test */
    public function actividades_sin_parametros()
    {
        $actividad = factory('App\Actividad', 4)
            ->create()
            ->each(function ($a) {
                $a->puntosEncuentro()->save(factory('App\PuntoEncuentro')->make());
            });

        $params = [];
        $response = $this->json('GET', '/ajax/actividades', $params);
        $response
            ->assertStatus(200)
            ->assertJsonStructure(
                [
                    "current_page",
                    "data" => [],
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

    /** @test */
    public function ver_detalle_de_actividad()
    {
        $actividad = factory('App\Actividad')->create();

        $this->get('/ajax/actividades/' . $actividad->idActividad)
            ->assertStatus(200)
            ->assertJsonStructure(
                [
                    "data" => [
                        "idActividad",
                        "tipo" => [
                            "idTipo",
                            "nombre"
                        ],
                        "fecha",
                        "hora",
                        "fechaInicio",
                        "fechaFin",
                        "fechaInicioInscripciones",
                        "fechaFinInscripciones",
                        "nombreActividad",
                        "descripcion",
                        "compromiso",
                        "lugar",
                        "moneda",
                        "puntosEncuentro" => []
                    ]
                ]
            );
    }
}
