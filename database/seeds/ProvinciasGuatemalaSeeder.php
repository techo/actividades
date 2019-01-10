<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProvinciasGuatemalaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $pais = App\Pais::where('nombre', 'Guatemala')->first();

        $listado_provincias = [
            'Petén',
            'Huehuetenango',
            'Quiché',
            'Alta Verapaz',
            'Izabal',
            'San Marcos',
            'Quetzaltenango',
            'Totonicapán',
            'Sololá',
            'Chimaltenango',
            'Sacatepéquez',
            'Guatemala',
            'Baja Verapaz',
            'El Progreso',
            'Jalapa',
            'Zacapa',
            'Chiquimula',
            'Retalhuleu',
            'Suchitepéquez',
            'Escuintla',
            'Santa Rosa',
            'Jutiapa'
        ];

        foreach ($listado_provincias as $v) {
            $p = new App\Provincia;
            $p->provincia = $v;
            $p->id_pais = $pais->id;
            $p->save();
        }

    }
}