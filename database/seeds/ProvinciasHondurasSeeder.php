<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProvinciasHondurasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $pais = App\Pais::where('nombre', 'Honduras')->first();

        $listado_provincias = [
            'Islas de la Bahía',
            'Cortés',
            'Atlántida',
            'Colón',
            'Gracias a Dios',
            'Copán',
            'Santa Bárbara',
            'Yoro',
            'Olancho',
            'Ocotepeque',
            'Lempira',
            'Intibucá',
            'Comayagua',
            'Francisco Morazán',
            'El Paraíso',
            'La Paz',
            'Valle',
            'Choluteca'
        ];

        foreach ($listado_provincias as $v) {
            $p = new App\Provincia;
            $p->provincia = $v;
            $p->id_pais = $pais->id;
            $p->save();
        }

    }
}