<?php

namespace Tests\Feature\ajax;

use App\CategoriaActividad;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ajaxCategoriasTest extends TestCase
{
    use DatabaseTransactions;

    public function test_categorias_por_id()
    {
        
        $categoria = factory(\App\CategoriaActividad::class)->create();

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

    public function test_categorias()
    {
        
        $categoria = factory(\App\CategoriaActividad::class,2)->create();

        $response = $this->get('/ajax/categorias/');

        $response->assertStatus(200)->assertJsonCount(2);
    }
}
