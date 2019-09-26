<?php

use Illuminate\Database\Seeder;
use \Spatie\Permission\Models\Permission as Permission;

class PermissionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Permission::create(['name' => 'crear_actividad']);
        Permission::create(['name' => 'editar_actividad']);
        Permission::create(['name' => 'borrar_actividad']);
        Permission::create(['name' => 'tomar_asistencia']);
        Permission::create(['name' => 'control_pagos']);
        Permission::create(['name' => 'ver_mis_actividades']);
        Permission::create(['name' => 'ver_backoffice']);
        Permission::create(['name' => 'asignar_roles']);
        Permission::create(['name' => 'administrar_imagenes']);
        Permission::create(['name' => 'ver_usuarios']);
        Permission::create(['name' => 'editar_usuarios']);
        Permission::create(['name' => 'borrar_usuarios']);
    }
}
