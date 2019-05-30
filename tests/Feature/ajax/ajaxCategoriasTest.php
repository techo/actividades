<?php

namespace Tests\Feature\ajax;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;

class ajaxCategoriasTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function invitado_puede_ver_categorias()
    {
        $this->withoutExceptionHandling();

        $categoria = factory('App\CategoriaActividad')->create();

        $this->get('/ajax/categorias/'.$categoria->id)
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
