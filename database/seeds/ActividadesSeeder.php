<?php

use Illuminate\Database\Seeder;

class ActividadesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory('App\Actividad',3)
                    ->create([
                        'ficha_medica_campos' => [],
                        'roles_tags' => [],
                        'actividades_tags' => [],
                        'tipo_inscriptos_tag' => [],
                    ])
                    ->each(function ($a) {
                        $a->puntosEncuentro()->save(factory(\App\PuntoEncuentro::class)->make());
                });
    }
}