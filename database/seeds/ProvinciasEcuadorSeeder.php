<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProvinciasEcuadorSeeder extends Seeder
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
            'Azuay' => [],
            'Bolívar' => [],
            'Cañar' => [],
            'Carchi' => [],
            'Chimborazo' => [],
            'Cotopaxi' => [],
            'El Oro' => [],
            'Esmeraldas' => [],
            'Galápagos' => [],
            'Guayas' => [],
            'Imbabura' => [],
            'Loja' => [],
            'Los Ríos' => [],
            'Manabí' => [],
            'Morona Santiago' => [],
            'Napo' => [],
            'Orellana' => [],
            'Pastaza' => [],
            'Pichincha' => [],
            'Santa Elena' => [],
            'Santo Domingo de los Tsáchilas' => [],
            'Sucumbíos' => [],
            'Tungurahua' => [],
            'Zamora Chinchipe' => [],
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