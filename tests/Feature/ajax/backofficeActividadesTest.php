<?php

namespace Tests\Feature\ajax;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class backofficeActividadesTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function test_listar_todas_las_actividades()
    {
        $response = $this->get('/admin/ajax/actividades?sort=nombre|ac');
        $response
            ->assertStatus(200)
            ->assertJsonStructure(
                [
                    "current_page",
                    "data",
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

