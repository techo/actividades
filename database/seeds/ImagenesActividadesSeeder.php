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
            ->update(['imagen' => '/img/Actividades-en-asentamiento.jpg',
                'descripcion' => 'Acompañanos y trabajemos junto a cientos de vecinos y voluntarios que luchan día a día para transformar la realidad de cientos de barrios de todo el país. No hace falta conocimientos previos, solo tu voluntad y ganas de participar.',
              'color'=>'#448ae1']);

        DB::table('atl_CategoriaActividad')
            ->where('id', 2)
            ->update(['imagen' => '/img/Actividades-en-oficina.jpg',
                'descripcion' => 'Acercate a nuestras oficinas en todo el país, conocenos y participá de actividades junto a todo el equipo. Vení a sacarte todas las dudas ¡Te esperamos!',
              'color'=>'#d86644']);

        DB::table('atl_CategoriaActividad')
            ->where('id', 3)
            ->update(['imagen' => '/img/eventos-especiales.png',
                'descripcion' => 'Porque a veces nos disfrazamos, o corremos, o corremos disfrazados, o participamos de otros eventos que no encajan bien en ningún lado. Eventos que son tan especiales que tuvimos que hacer una sección especialmente para ellos.',
              'color'=>'#c78a00']);

        DB::table('Tipo')
            ->where('idCategoria', 1)
            ->update(['imagen' => '/img/Actividades-en-asentamiento.jpg']);

        DB::table('Tipo')
            ->where('idCategoria', 2)
            ->update(['imagen' => '/img/Actividades-en-oficina.jpg']);

        DB::table('Tipo')
            ->where('idCategoria', 3)
            ->update(['imagen' => '/img/eventos-especiales.png']);
    }
}
