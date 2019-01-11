<?php

namespace Tests\Feature\ajax;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Faker\Factory;
use App\Persona;
use Carbon\Carbon;

class backofficeUsuarioCaracterInternacionalTest extends TestCase
{

    use DatabaseTransactions;
    use WithoutMiddleware;

    public function test_crear_usuario_caracter_internacinoal()
    {

        factory(\App\Persona::class)->create([ 'nombres' => 'ñombré', 'apellidoPaterno' => 'aṕellidó']);

        $response = $this->get('/admin/ajax/search/usuarios?usuario=ñombré');

        $response
         ->assertStatus(200)
         ->assertJsonStructure(
             [
               '*' => [
                 "idPersona",
                 "dni",
                 "nombre"
               ]
             ]
         );
        }
}
