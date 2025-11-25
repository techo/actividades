<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InitialDataSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | TABLE: permissions
        |--------------------------------------------------------------------------
        */
        DB::table('permissions')->truncate();

        DB::table('permissions')->insert([
            ['id' => 1,'name' => 'crear_actividad','guard_name' => 'web','created_at' => '2018-10-11 00:35:49','updated_at' => '2018-10-11 00:35:49'],
            ['id' => 2,'name' => 'editar_actividad','guard_name' => 'web','created_at' => '2018-10-11 00:35:49','updated_at' => '2018-10-11 00:35:49'],
            ['id' => 3,'name' => 'borrar_actividad','guard_name' => 'web','created_at' => '2018-10-11 00:35:49','updated_at' => '2018-10-11 00:35:49'],
            ['id' => 4,'name' => 'tomar_asistencia','guard_name' => 'web','created_at' => '2018-10-11 00:35:49','updated_at' => '2018-10-11 00:35:49'],
            ['id' => 5,'name' => 'control_pagos','guard_name' => 'web','created_at' => '2018-10-11 00:35:49','updated_at' => '2018-10-11 00:35:49'],
            ['id' => 6,'name' => 'ver_mis_actividades','guard_name' => 'web','created_at' => '2018-10-11 00:35:49','updated_at' => '2018-10-11 00:35:49'],
            ['id' => 7,'name' => 'ver_backoffice','guard_name' => 'web','created_at' => '2018-10-11 00:35:49','updated_at' => '2018-10-11 00:35:49'],
            ['id' => 8,'name' => 'asignar_roles','guard_name' => 'web','created_at' => '2018-10-11 00:35:49','updated_at' => '2018-10-11 00:35:49'],
            ['id' => 9,'name' => 'administrar_imagenes','guard_name' => 'web','created_at' => '2018-10-11 00:35:49','updated_at' => '2018-10-11 00:35:49'],
            ['id' => 10,'name' => 'ver_usuarios','guard_name' => 'web','created_at' => '2018-10-11 00:35:49','updated_at' => '2018-10-11 00:35:49'],
            ['id' => 11,'name' => 'editar_usuarios','guard_name' => 'web','created_at' => '2019-07-03 17:36:33','updated_at' => '2019-07-03 17:36:33'],
            ['id' => 12,'name' => 'borrar_usuarios','guard_name' => 'web','created_at' => '2019-10-08 20:24:48','updated_at' => '2019-10-08 20:24:48'],
        ]);


        /*
        |--------------------------------------------------------------------------
        | TABLE: atl_CategoriaActividad
        |--------------------------------------------------------------------------
        */
        DB::table('atl_CategoriaActividad')->truncate();

        DB::table('atl_CategoriaActividad')->insert([
            [ 'id' => 1, 'slug' => 'to_act', 'descripcion' => 'Acompañanos y trabajemos junto a cientos de vecinos y voluntarios que luchan día a día para transformar la realidad de cientos de barrios de todo el país. No hace falta conocimientos previos, solo tu voluntad y ganas de participar.', 'img' => '/img/Actividades-en-asentamiento.jpg', 'color' => '#009fe3'],
            [ 'id' => 2, 'slug' => 'to_reflect_and_learn', 'descripcion' => 'Acercate a nuestras oficinas en todo el país, conocenos y participá de actividades junto a todo el equipo. Vení a sacarte todas las dudas ¡Te esperamos!', 'img' => '/img/Actividades-en-oficina.jpg', 'color' => '#f088b6'],
            [ 'id' => 3, 'slug' => 'especial_events', 'descripcion' => 'Porque a veces nos disfrazamos, o corremos, o corremos disfrazados, o participamos de otros eventos que no encajan bien en ningún lado. Eventos que son tan especiales que tuvimos que hacer una sección especialmente para ellos.', 'img' => '/img/eventos-especiales.png', 'color' => '#954B97'],
            [ 'id' => 4, 'slug' => 'online_events', 'descripcion' => 'Actividades Online', 'img' => '/img/eventos-especiales.png', 'color' => '#fdc533'],
            [ 'id' => 5, 'slug' => 'campañas', 'descripcion' => 'Campañas', 'img' => '/img/eventos-especiales.png', 'color' => '#e94362'],
            [ 'id' => 6, 'slug' => 'application', 'descripcion' => 'application', 'img' => '/img/eventos-especiales.png', 'color' => '#e94362'],
        ]);


        /*
        |--------------------------------------------------------------------------
        | TABLE: role_has_permissions
        |--------------------------------------------------------------------------
        */
        DB::table('role_has_permissions')->truncate();

        DB::table('role_has_permissions')->insert([
            ['permission_id' => 1, 'role_id' => 2],
            ['permission_id' => 2, 'role_id' => 2],
            ['permission_id' => 3, 'role_id' => 2],
            ['permission_id' => 4, 'role_id' => 2],
            ['permission_id' => 5, 'role_id' => 2],
            ['permission_id' => 6, 'role_id' => 2],
            ['permission_id' => 7, 'role_id' => 2],
            ['permission_id' => 8, 'role_id' => 2],
            ['permission_id' => 9, 'role_id' => 2],
            ['permission_id' => 10, 'role_id' => 2],
            ['permission_id' => 11, 'role_id' => 2],
            ['permission_id' => 12, 'role_id' => 2],
            ['permission_id' => 1, 'role_id' => 3],
            ['permission_id' => 2, 'role_id' => 3],
            ['permission_id' => 3, 'role_id' => 3],
            ['permission_id' => 4, 'role_id' => 3],
            ['permission_id' => 5, 'role_id' => 3],
            ['permission_id' => 6, 'role_id' => 3],
            ['permission_id' => 7, 'role_id' => 3],
            ['permission_id' => 9, 'role_id' => 3],
            ['permission_id' => 10, 'role_id' => 3],
        ]);


        /*
        |--------------------------------------------------------------------------
        | TABLE: Tipo
        |--------------------------------------------------------------------------
        */
        DB::table('Tipo')->truncate();

        DB::table('Tipo')->insert([
            [
                'id' => 11,
                'nombre' => 'Detección y Asignación',
                'categoria_id' => 1,
                'descripcion' => "Es el equipo que se encarga de co-gestionar junto con los vecinos el Programa de Construcción de Viviendas de Emergencia en los barrios. Las tareas del área son encuestar a las familias de los barrios donde trabajamos con el Programa para conocer su situación, analizar las encuestas y priorizar a las familias junto a las que vamos a construir y llevar adelante la organización de la construcción junto con la mesa de trabajo del barrio o junto a un grupo promotor de vecinos.\nNo es necesario tener conocimientos previos. Se trabaja siempre en grupo y TECHO se encarga de capacitar a los voluntarios/as.",
                'img' => '/img/Actividades-en-asentamiento.jpg',
                'color' => null
            ]
        ]);
    }
}
