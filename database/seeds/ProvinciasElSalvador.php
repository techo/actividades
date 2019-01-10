<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProvinciasElSalvadorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $pais = App\Pais::where('nombre', 'El Salvador')->first();

        $listado_provincias = [
            'San Salvador',
            'La Libertad',
            'Santa Ana',
            'Sonsonate',
            'San Miguel',
            'Usulután',
            'Ahuachapán',
            'La Paz',
            'La Unión',
            'Cuscatlán',
            'Chalatenango',
            'Morazán',
            'San Vicente',
            'Cabañas'
        ];

        foreach ($listado_provincias as $v) {
            $p = new App\Provincia;
            $p->provincia = $v;
            $p->id_pais = $pais->id;
            $p->save();
        }

    }
}