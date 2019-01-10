<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProvinciasChileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $pais = App\Pais::where('nombre', 'Chile')->first();

        $listado_provincias = [
            "Arica y Parinacota y Tarapacá",
            "Antofagasta",
            "Atacama y Coquimbo",
            "Valparaíso",
            "O'Higgins",
            "Maule",
            "Ñuble, Biobío y La Araucanía (norte)",
            "La Araucanía (sur)",
            "Los Ríos y Los Lagos (norte)",
            "Los Lagos (sur) y Aysén",
            "Magallanes",
            "Metropolitana de Santiago"
        ];

        foreach ($listado_provincias as $v) {
            $p = new App\Provincia;
            $p->provincia = $v;
            $p->id_pais = $pais->id;
            $p->save();
        }

    }
}