<?php

namespace Tests\Feature\ajax;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class ajaxProvinciasTest extends TestCase
{
    use RefreshDatabase;
    
    /**
     * A basic test example.
     *
     * @return void
     */
    public function test_ver_detalle_de_provincia()
    {
        $provincia = factory('App\Provincia')->create();
        $response = $this->get('/ajax/provincias/'.$provincia->id);

        $response
            ->assertStatus(200)
            ->assertJsonStructure(
                [
                    'id',
                    'provincia',
                    'id_pais',
                    'localidades' => [
                        '*' => [
                            'id',
                            'id_provincia',
                            'localidad'
                        ]
                    ]
                ]
            );
    }
}
