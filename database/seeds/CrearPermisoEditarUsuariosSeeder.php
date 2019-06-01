<?php

use Illuminate\Database\Seeder;
use \Spatie\Permission\Models\Permission as Permission;

class CrearPermisoEditarUsuariosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Permission::create(['name' => 'editar_usuarios']);
    }
}
