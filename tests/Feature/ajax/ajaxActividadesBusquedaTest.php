<?php

namespace Tests\Feature\ajax;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ajaxActividadesBusqueda extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function invitado_puede_buscar_por_localidad_de_punto()
    {
        $this->withoutExceptionHandling();

        $actividades = factory('App\Actividad', 4)
            ->create()
            ->each(function ($a) {
                $a->puntosEncuentro()->save(factory('App\PuntoEncuentro')->make());
            });

        $params = [
        	 'busqueda' => "punto",
			// 'categoria' => $actividades[0]->tipo()->first()->idCategoria,
			'localidades' => [$actividades[0]->puntosEncuentro()->first()->idLocalidad],
            // 'provincias' => null,
			// 'tipos' => []
        ];

        $response = $this->post('/ajax/actividades', $params);

        $response
            ->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    /** @test */
    public function invitado_puede_buscar_por_localidad_lugar()
    {
        $this->withoutExceptionHandling();

        $actividades = factory('App\Actividad', 4)
            ->create()
            ->each(function ($a) {
                $a->puntosEncuentro()->save(factory('App\PuntoEncuentro')->make());
            });

        $params = [
            'busqueda' => "lugar",
            // 'categoria' => $actividades[0]->tipo()->first()->idCategoria,
            'localidades' => [$actividades[0]->idLocalidad],
            // 'provincias' => null,
            // 'tipos' => []
        ];

        $this->post('/ajax/actividades', $params)
            ->assertStatus(200)
            ->assertJsonCount(1, 'data');

    }

    /** @test */
    public function invitado_puede_buscar_por_provincia_punto()
    {
        $this->withoutExceptionHandling();

        $actividades = factory('App\Actividad', 4)
            ->create()
            ->each(function ($a) {
                $a->puntosEncuentro()->save(factory('App\PuntoEncuentro')->make());
            });

        $params = [
            'busqueda' => "punto",
            //'categoria' => $actividades[0]->tipo()->first()->idCategoria,
            // 'localidades' => [],
            'provincias' => [$actividades[0]->puntosEncuentro()->first()->idProvincia],
            // 'tipos' => []
        ];

        $response = $this->post('/ajax/actividades', $params);

        $response
            ->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    /** @test */
    public function invitado_puede_buscar_por_provincia_lugar()
    {
        $this->withoutExceptionHandling();

        $actividades = factory('App\Actividad', 4)
            ->create()
            ->each(function ($a) {
                $a->puntosEncuentro()->save(factory('App\PuntoEncuentro')->make());
            });

        $params = [
            'busqueda' => "lugar",
            //'categoria' => $actividades[0]->tipo()->first()->idCategoria,
            // 'localidades' => [],
            'provincias' => [$actividades[0]->idProvincia],
            // 'tipos' => []
        ];

        $response = $this->post('/ajax/actividades', $params);

        $response
            ->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    /** @test */
    public function invitado_puede_buscar_por_tipo()
    {
        $this->withoutExceptionHandling();

            $actividades = factory('App\Actividad', 4)
            ->create()
            ->each(function ($a) {
                $a->puntosEncuentro()->save(factory('App\PuntoEncuentro')->make());
            });

        $params = [
            // 'busqueda' => "punto",
            // 'categoria' => $actividades[0]->tipo()->first()->idCategoria,
            // 'localidades' => [],
            // 'provincias' => [],
            'tipos' => [$actividades[0]->tipo()->first()->idTipo]
        ];

        $response = $this->post('/ajax/actividades', $params);

        $response
            ->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    /** @test */
    public function invitado_puede_buscar_por_categoria()
    {
        $this->withoutExceptionHandling();

        $actividades = factory('App\Actividad', 4)
            ->create()
            ->each(function ($a) {
                $a->puntosEncuentro()->save(factory('App\PuntoEncuentro')->make());
            });

        $params = [
            // 'busqueda' => "punto",
            'categoria' => $actividades[0]->tipo()->first()->idCategoria,
            // 'localidades' => [],
            // 'provincias' => [],
            // 'tipos' => []
        ];

        $response = $this->post('/ajax/actividades', $params);

        $response
            ->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    /** @test */
    public function invitado_puede_buscar_sin_filtros()
    {
        $this->withoutExceptionHandling();

        $actividades = factory('App\Actividad', 4)
            ->create()
            ->each(function ($a) {
                $a->puntosEncuentro()->save(factory('App\PuntoEncuentro')->make());
            });

        $params = [
            // 'busqueda' => "punto",
            // 'categoria' => null,
            'localidades' => null,
            'provincias' => [],
            'tipos' => []
        ];

        $response = $this->post('/ajax/actividades', $params);

        $response
            ->assertStatus(200)
            ->assertJsonCount(4, 'data');
    }

    /** @test */
    public function invitado_puede_buscar_actividades_sin_localidad_lugar()
    {
        $this->withoutExceptionHandling();

        $actividades = factory('App\Actividad', 4)
            ->create([ 'idLocalidad' => null ])
            ->each(function ($a) {
                $a->puntosEncuentro()->save(factory('App\PuntoEncuentro')->make());
            });

        $params = [
            'busqueda' => "lugar",
            // 'categoria' => null,
            'localidades' => null,
            'provincias' => [],
            'tipos' => []
        ];

        $response = $this->post('/ajax/actividades', $params);

        $response
            ->assertStatus(200)
            ->assertJsonCount(4, 'data');
    }

    /** @test */
    public function invitado_puede_buscar_actividades_sin_localidad_punto()
    {
        $this->withoutExceptionHandling();

        $actividades = factory('App\Actividad', 4)
            ->create()
            ->each(function ($a) {
                $a->puntosEncuentro()->save(factory('App\PuntoEncuentro')->make([ 'idLocalidad' => null ]));
            });

        $params = [
            'busqueda' => "punto",
            'localidades' => null,
        ];

        $response = $this->post('/ajax/actividades', $params);

        $response
            ->assertStatus(200)
            ->assertJsonCount(4, 'data');
    }
}