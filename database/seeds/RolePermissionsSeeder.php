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
        $admin = Role::findByName('admin');

        $permissions = Permission::all();

        $admin->givePermissionTo($permissions);

        $coordinador = Role::findByName('coordinador');

        $permissions = Permission::whereIn('name',
            [
                'crear_actividad',
                'editar_actividad',
                'borrar_actividad',
                'tomar_asistencia',
                'control_pagos',
                'ver_mis_actividades',
                'ver_backoffice',
                'administrar_imagenes',
                'ver_usuarios',
            ])->get();

        $coordinador->givePermissionTo($permissions);
    }
}
