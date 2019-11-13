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
        $pais = App\Pais::where('nombre', 'Ecuador')->first();

        $listado_divisiones = [
            'Arica y Parinacota' => [],
            'Tarapacá' => [],
            'Antofagasta' => [],
            'Atacama' => [],
            'Coquimbo' => [],
            'Valparaíso' => [],
            'Libertador GeneralBernardo O\'Higgins' => [],
            'Maule' => [],
            'Ñuble' => [],
            'Biobío' => [],
            'Araucanía' => [],
            'Los Ríos' => [],
            'Los Lagos' => [],
            'Aysén del GeneralCarlos Ibáñez del Campo' => [],
            'Magallanes y de laAntártica Chilena' => [],
            'Metropolitana de Santiago' => [],
        ];

        foreach ($listado_divisiones as $k => $v) {
            $p = new App\Provincia;
            $p->provincia = $k;
            $p->id_pais = $pais->id;
            $p->save();

            foreach ($v as $vv) {
                $s = new App\Localidad;
                $s->localidad = $vv;
                $s->id_provincia = $p->id;
                $s->save();
            }
        }

    }
}