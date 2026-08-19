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

        // `enviar_comunicaciones` NO va al rol admin: es un permiso acotado a
        // usuarios puntuales (cuentas @admin.org, una por país). Ver la migración
        // 2026_08_19_120000_restringe_enviar_comunicaciones_a_admin_org.
        $permissions = Permission::where('name', '!=', 'enviar_comunicaciones')->get();

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
