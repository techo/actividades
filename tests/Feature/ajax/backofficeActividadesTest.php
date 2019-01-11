<?php

namespace Tests\Feature\ajax;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Faker\Generator as Faker;
use Carbon\Carbon;
use Faker\Factory;

class backofficeActividadesTest extends TestCase
{
    use DatabaseTransactions;

    public function test_listar_todas_las_actividades()
    {

        $faker = Factory::create();

        $request = new \Illuminate\Http\Request();
        $request->setMethod('POST');
        $request->request->add([
            'email' => $faker->unique()->safeEmail ,
            'nombre' => $faker->firstName,
            'apellido' => $faker->lastName,
            'dni' => '11111111',
            'pais' => factory(\App\Pais::class)->create(),
            'rol' => ['rol' => 'admin'],
            'nacimiento' => Carbon::createFromFormat('d-m-Y', '01-01-1971'),
            'telefono' => '1',
            'sexo' => ['id' => 'M']
        ]);

        $userService = new \App\Http\Services\UserService();

        $validator = $userService->createValidator($request); 

        if($validator->passes()){
            if($usuario = $userService->crearUsuario($request)){
                $usuario->password = $userService->setPassword('password');
                $usuario->save();
            }
        }

        $response = $this->post('/login', [
            'email' => $usuario->mail,
            'password' => 'password'
        ]);

        $response = $this->actingAs($usuario)->get('/admin/ajax/actividades?sort=nombreActividad|asc');
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

