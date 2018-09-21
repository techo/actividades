<?php

use Illuminate\Database\Seeder;

class adminSeeder extends Seeder
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
            'email' => 'admin@admin.com',
            'nombre' => 'admin',
            'apellido' => 'admin',
            'dni' => '11111111',
            'pais' => \App\Pais::where('nombre', 'argentina')->first(),
            'rol' => ['rol' => 'admin'],
            'nacimiento' => Carbon\Carbon::createFromFormat('d-m-Y', '01-01-1971'),
            'telefono' => '1',
            'sexo' => ['id' => 'M']
        ]);

        $userService = new \App\Http\Services\UserService();

        $validator = $userService->createValidator($request);

        if($validator->passes()){
            if($usuario = $userService->crearUsuario($request)){
                $usuario->password = $userService->setPassword('admin');
                $usuario->save();
                echo "Usuario admin creado correctamente. Ingrese con: \n Usuario: admin@admin.com \n Contraseña: admin\n";
                return;
            }
            echo 'Ocurrió un problema al crear usuario admin';
            return;
        }

        echo "Falló la validación:\n";

        foreach ($validator->errors()->all() as $error) {
            echo "$error \n";
        }
        return;
    }
}
