<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoriaActividadesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        
        DB::statement("INSERT INTO `atl_CategoriaActividad` (`id`, `nombre`, `descripcion`, `imagen`, `color`)
                VALUES
                    (1,'Actividades en Asentamientos','Acompañanos y trabajemos junto a cientos de vecinos y voluntarios que luchan día a día para transformar la realidad de cientos de barrios de todo el país. No hace falta conocimientos previos, solo tu voluntad y ganas de participar.', '/img/Actividades-en-asentamiento.jpg', '#0092dd'),
                    (2,'Actividades de Oficina y Formación','Acercate a nuestras oficinas en todo el país, conocenos y participá de actividades junto a todo el equipo. Vení a sacarte todas las dudas ¡Te esperamos!','/img/Actividades-en-oficina.jpg', '#ea6d4f'),
                    (3,'Eventos Especiales','Porque a veces nos disfrazamos, o corremos, o corremos disfrazados, o participamos de otros eventos que no encajan bien en ningún lado. Eventos que son tan especiales que tuvimos que hacer una sección especialmente para ellos.','/img/eventos-especiales.png', '#d78e01');
            ");
    }
}