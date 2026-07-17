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
        $persona = factory('App\Persona')->create([
            'nombres' => 'Coordinador',
            'mail' => 'coordinador@coordinador.com',
            'password' => Hash::make('coordinador'),
            // País real (no el fake del factory): el login usa el país del usuario.
            'idPais' => 13,
        ]);
        $persona->assignRole('coordinador');
    }
}
