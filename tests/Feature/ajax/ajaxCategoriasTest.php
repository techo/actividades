<?php

namespace Tests\Feature\ajax;

use App\CategoriaActividad;
use Tests\TestCase;

class ajaxCategoriasTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testExample()
    {
        $categoria = CategoriaActividad::first();
        $response = $this->get('/ajax/categorias/'.$categoria->id);

        $response
            ->assertStatus(200)
            ->assertJsonStructure(
                [
                    'id',
                    'nombre',
                    'descripcion',
                    'tipos' => [
                        '*' => [
                            'idTipo',
                            'nombre',
                            'hs',
                            'fyv',
                            'alias',
                            'idCategoria',
                        ]
                    ]
                ]
            );
    }
}
