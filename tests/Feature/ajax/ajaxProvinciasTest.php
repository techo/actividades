<?php

namespace Tests\Feature\ajax;

use App\Provincia;
use Tests\TestCase;

class ajaxProvinciasTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function test_ver_detalle_de_provincia()
    {
        $provincia = Provincia::first();
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
