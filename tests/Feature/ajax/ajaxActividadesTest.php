<?php

namespace Tests\Feature\ajax;

use App\Actividad;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ajaxActividadesTest extends TestCase
{

    use DatabaseTransactions;

    public function test_tipos_de_actividad_sin_parametros()
    {
        factory(\App\Actividad::class)->create();

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
        factory(\App\Provincia::class)->create();

        $params = [];

        $response = $this->post('/ajax/actividades/provincias/', $params);

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

    public function test_actividades_sin_parametros()
    {
        factory(\App\Actividad::class)->create();

        $params = [];

        $response = $this->post('/ajax/actividades', $params);

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

    public function test_ver_detalle_de_actividad()
    {
        $actividad = factory(\App\Actividad::class)->create();

        $url = '/ajax/actividades/' . $actividad->idActividad;

        $response = $this->get($url);

        $response
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

    public function test_ajax_coordinadores()
    {
        factory(\App\Actividad::class)->create([ 'idCoordinador' => factory(\App\Persona::class)->create([ 'nombres' => 'prueba', 'apellidoPaterno' => 'prueba'])->idPersona ]);

        $url = '/ajax/coordinadores/?coordinador=prueba';

        $response = $this->get($url);

        $response
            ->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }
}
