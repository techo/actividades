<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProvinciasCostaRicaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $pais = App\Pais::where('nombre', 'Costa Rica')->first();

        $listado_provincias = [
            'San José',
            'Alajuela',
            'Cartago',
            'Heredia',
            'Guanacaste',
            'Puntarenas',
            'Limón'
        ];

        foreach ($listado_provincias as $v) {
            $p = new App\Provincia;
            $p->provincia = $v;
            $p->id_pais = $pais->id;
            $p->save();
        }

    }
}