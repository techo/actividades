<?php

use Illuminate\Database\Seeder;
use \Spatie\Permission\Models\Role as Role;
use \Spatie\Permission\Models\Permission as Permission;

class RolePermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $rol = Role::findByName('admin');

        $permissions = Permission::all();

        $rol->givePermissionTo($permissions);

        $rol = Role::findByName('coordinador');

        $permissions = Permission::whereIn('name', ['crear_actividad', 'tomar_asistencia', 'control_pagos', 'ver_mis_actividades'])->get();

        $rol->givePermissionTo($permissions);
    }
}
