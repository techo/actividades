<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProvinciasArgentinaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        
        if (Schema::hasTable('atl_provincias')) {
            DB::statement("INSERT IGNORE INTO `atl_provincias` (`id`, `provincia`, id_pais) VALUES
                (1, 'Buenos Aires',13),
                (2, 'Buenos Aires-GBA',13),
                (3, 'Capital Federal',13),
                (4, 'Catamarca',13),
                (5, 'Chaco',13),
                (6, 'Chubut',13),
                (7, 'Córdoba',13),
                (8, 'Corrientes',13),
                (9, 'Entre Ríos',13),
                (10, 'Formosa',13),
                (11, 'Jujuy',13),
                (12, 'La Pampa',13),
                (13, 'La Rioja',13),
                (14, 'Mendoza',13),
                (15, 'Misiones',13),
                (16, 'Neuquén',13),
                (17, 'Río Negro',13),
                (18, 'Salta',13),
                (19, 'San Juan',13),
                (20, 'San Luis',13),
                (21, 'Santa Cruz',13),
                (22, 'Santa Fe',13),
                (23, 'Santiago del Estero',13),
                (24, 'Tierra del Fuego',13),
                (25, 'Tucumán',13);
            ");
        }
    }
}