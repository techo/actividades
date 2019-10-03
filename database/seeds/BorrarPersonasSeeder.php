<?php

use Illuminate\Database\Seeder;
use \Spatie\Permission\Models\Role as Role;
use \Spatie\Permission\Models\Permission as Permission;

class BorrarPersonasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permission = Permission::create(['name' => 'borrar_usuarios']);

        $admin = Role::findByName('admin');

        $admin->givePermissionTo($permission);
    }
}
