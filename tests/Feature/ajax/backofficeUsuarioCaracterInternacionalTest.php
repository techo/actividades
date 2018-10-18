<?php

namespace Tests\Feature\ajax;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Faker\Factory;
use App\Persona;
use Carbon\Carbon;

class backofficeUsuarioCaracterInternacionalTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
     public function test_crear_usuario_caracter_internacinoal()
     {

         $faker = Factory::create();
         $request = new \Illuminate\Http\Request();
         $request->setMethod('POST');
         $request->request->add([
             'email' => $faker->unique()->safeEmail ,
             'nombre' => 'ñombré',
             'apellido' => 'ápellidoñ',
             'dni' => '11111111',
             'pais' => \App\Pais::where('nombre', 'argentina')->first(),
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
         $response = $this->actingAs($usuario)->get('/admin/ajax/search/usuarios?usuario=ñombré');
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
