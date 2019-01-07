<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProvinciasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $pais = App\Pais::where('nombre', 'Uruguay')->first();

        $listado_provincias = [
            'Montevideo',
            'Artigas',
            'Canelones',
            'Cerro Largo',
            'Colonia',
            'Durazno',
            'Flores',
            'Florida',
            'Lavalleja',
            'Maldonado',
            'Paysandú',
            'Rio Negro',
            'Rivera',
            'Rocha',
            'Salto',
            'San José',
            'Soriano',
            'Tacuarembó',
            'Treinta y Tres'
        ];

        foreach ($listado_provincias as $v) {
            //dd($v);
            $p = new App\Provincia;
            $p->provincia = $v;
            $p->id_pais = $pais->id;
            $p->save();

            $l = new App\Localidad;
            $l->localidad = 'Sin especificar';
            $l->id_provincia = $p->id;
            $l->save();
        }

    }
}