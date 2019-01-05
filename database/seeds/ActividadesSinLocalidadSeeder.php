<?php

use Illuminate\Database\Seeder;

class ActividadesSinLocalidadSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $p = factory(\App\Provincia::class,2)->create();

        factory(\App\Actividad::class,5)->create(['idLocalidad' => null, 'idProvincia' => $p[0]->id])->each(function ($a) { $a->puntosEncuentro()->save(factory(\App\PuntoEncuentro::class)->make(['idLocalidad' => null, 'idProvincia' => $a->idProvincia]));});

        factory(\App\Actividad::class,5)->create(['idLocalidad' => null, 'idProvincia' => $p[1]->id])->each(function ($a) { $a->puntosEncuentro()->save(factory(\App\PuntoEncuentro::class)->make(['idLocalidad' => null, 'idProvincia' => $a->idProvincia]));});
    }
}

