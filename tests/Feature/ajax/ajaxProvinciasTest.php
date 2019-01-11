<?php

namespace Tests\Feature\ajax;

use App\Provincia;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ajaxProvinciasTest extends TestCase
{

    use DatabaseTransactions;

    public function test_ver_detalle_de_provincia()
    {
        
        $provincia = factory(\App\Provincia::class)->create();

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
