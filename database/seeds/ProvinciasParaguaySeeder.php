<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProvinciasParaguaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $pais = App\Pais::where('nombre', 'Paraguay')->first();

        $listado_divisiones = [
            'Distrito Capital' => [],
            'Concepción' => [],
            'San Pedro' => [],
            'Cordillera' => [],
            'Guairá' => [],
            'Caaguazú' => [],
            'Caazapá' => [],
            'Itapúa' => [],
            'Misiones' => [],
            'Paraguarí' => [],
            'Alto Paraná' => [],
            'Central' => [],
            'Ñeembucú' => [],
            'Amambay' => [],
            'Canindeyú' => [],
            'Presidente Hayes' => [],
            'Alto Paraguay' => [],
            'Boquerón' => [],
            'Paraguay' => [],
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