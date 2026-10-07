<?php

namespace Tests\Feature;

use App\Actividad;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * La pantalla 500 con marca (Handler::render500) es solo para errores de servidor
 * reales. Un "no encontrado" (ModelNotFound) o un "sin permiso" (Authorization)
 * todavía no son HttpException cuando llegan al Handler: antes se mostraban como
 * "Error 500 · Algo salió mal" (y sin log). Deben seguir siendo 404 / 403.
 */
class PantallaErrorStatusTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp()
    {
        parent::setUp();
        config(['app.debug' => false]); // como prod: sin Whoops

        Route::middleware('web')->group(function () {
            Route::get('/_test/model-not-found', function () {
                return Actividad::findOrFail(999999999);
            });
            Route::get('/_test/sin-permiso', function () {
                throw new AuthorizationException('nope');
            });
            Route::get('/_test/bug', function () {
                throw new \RuntimeException('bug real');
            });
        });
    }

    /** @test */
    public function model_not_found_es_404_no_500()
    {
        $this->get('/_test/model-not-found')->assertStatus(404);
    }

    /** @test */
    public function authorization_es_403_no_500()
    {
        $this->get('/_test/sin-permiso')->assertStatus(403);
    }

    /** @test */
    public function un_bug_real_sigue_mostrando_la_pantalla_500()
    {
        $this->get('/_test/bug')
            ->assertStatus(500)
            ->assertViewIs('errors.500');
    }
}
