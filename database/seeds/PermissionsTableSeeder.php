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
        // firstOrCreate (no create) para que el seeder sea idempotente: algunos permisos
        // ya los crea una migración (ej. `enviar_comunicaciones` en
        // 2026_08_18_120000_add_permiso_enviar_comunicaciones), y `migrate:fresh --seed`
        // corre las migraciones antes de sembrar. Con create() eso reventaba con
        // PermissionAlreadyExists y rompía la suite y `./dev.sh fresh`.
        $permisos = [
            'crear_actividad',
            'editar_actividad',
            'borrar_actividad',
            'tomar_asistencia',
            'control_pagos',
            'ver_mis_actividades',
            'ver_backoffice',
            'asignar_roles',
            'administrar_imagenes',
            'ver_usuarios',
            'editar_usuarios',
            'enviar_comunicaciones',
        ];

        foreach ($permisos as $permiso) {
            Permission::firstOrCreate(['name' => $permiso]);
        }
    }
}
