<?php

use Illuminate\Database\Seeder;

class UsuarioCoordinadorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $request = new \Illuminate\Http\Request();
        $request->setMethod('POST');
        $request->request->add([
            'email' => 'coordinador@coordinador.com',
            'nombre' => 'coordinador',
            'apellido' => 'coordinador',
            'dni' => '2222222',
            'pais' => factory(\App\Pais::class)->create(),
            'rol' => ['rol' => 'coordinador'],
            'nacimiento' => Carbon\Carbon::createFromFormat('d-m-Y', '01-01-1971'),
            'telefono' => '1',
            'sexo' => ['id' => 'M']
        ]);

        $userService = new \App\Http\Services\UserService();

        $validator = $userService->createValidator($request);

        if($validator->passes()){
            if($usuario = $userService->crearUsuario($request)){
                $usuario->password = $userService->setPassword('coordinador');
                $usuario->save();
                echo "Usuario coordinador creado correctamente. Ingrese con: \n Usuario: coordinador@coordinador.com \n Contraseña: coordinador\n";
                return;
            }
            echo 'Ocurrió un problema al crear usuario coordinador';
            return;
        }

        echo "Falló la validación:\n";

        foreach ($validator->errors()->all() as $error) {
            echo "$error \n";
        }
        return;
    }
}
