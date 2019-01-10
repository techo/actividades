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
        $pais = App\Pais::where('nombre', 'Colombia')->first();

        $listado_provincias = [
            'Concepción',
            'Pedro',
            'Cordillera',
            'Guairá',
            'Caaguazú',
            'Caazapá',
            'Itapúa',
            'Misiones',
            'Paraguarí',
            'Paraná',
            'Departamento Central Central',
            'Ñeembucú',
            'Amambay',
            'Canindeyú',
            'Hayes',
            'Paraguay',
            'Boquerón'
        ];

        foreach ($listado_provincias as $v) {
            $p = new App\Provincia;
            $p->provincia = $v;
            $p->id_pais = $pais->id;
            $p->save();
        }

    }
}