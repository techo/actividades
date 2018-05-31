<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ImagenesActividadesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('atl_CategoriaActividad')
            ->where('id', 1)
            ->update(['imagen' => '/img/Actividades-en-asentamiento.jpg']);

        DB::table('atl_CategoriaActividad')
            ->where('id', 2)
            ->update(['imagen' => '/img/Actividades-en-oficina.jpg']);

        DB::table('atl_CategoriaActividad')
            ->where('id', 3)
            ->update(['imagen' => '/img/tarjeta-1.jpg']);

        DB::table('Tipo')
            ->where('idCategoria', 1)
            ->update(['imagen' => '/img/Actividades-en-asentamiento.jpg']);

        DB::table('Tipo')
            ->where('idCategoria', 2)
            ->update(['imagen' => '/img/Actividades-en-oficina.jpg']);

        DB::table('Tipo')
            ->where('idCategoria', 3)
            ->update(['imagen' => '/img/tarjeta-1.jpg']);
    }
}