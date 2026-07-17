<?php

use Illuminate\Database\Seeder;

class UsuarioAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $persona = factory('App\Persona')->create([
            'nombres' => 'Administrador',
            'mail' => 'administrador@administrador.com',
            'password' => Hash::make('administrador'),
            // País real (no el fake del factory): el login usa el país del usuario.
            'idPais' => 13,
        ]);
        $persona->assignRole('admin');
    }
}
