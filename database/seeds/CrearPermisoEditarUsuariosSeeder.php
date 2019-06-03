<?php

use Illuminate\Database\Seeder;
use \Spatie\Permission\Models\Permission as Permission;
use \Spatie\Permission\Models\Role as Role;

class CrearPermisoEditarUsuariosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permission = Permission::create(['name' => 'editar_usuarios']);

        $admin = Role::findByName('admin');

        $admin->givePermissionTo($permission);
    }
}
