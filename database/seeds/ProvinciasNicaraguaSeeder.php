<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProvinciasNicaraguaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $pais = App\Pais::where('nombre', 'Nicaragua')->first();

        $listado_provincias = [
            'RACCN',
            'RACCS',
            'Boaco',
            'Carazo',
            'Chinandega',
            'Chontales',
            'Estelí',
            'Granada',
            'Jinotega',
            'León',
            'Madriz',
            'Managua',
            'Masaya',
            'Matagalpa',
            'Segovia',
            'Juan',
            'Rivas'
        ];

        foreach ($listado_provincias as $v) {
            $p = new App\Provincia;
            $p->provincia = $v;
            $p->id_pais = $pais->id;
            $p->save();
        }

    }
}