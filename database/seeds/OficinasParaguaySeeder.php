<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OficinasParaguaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $listado_oficinas = [
            'Central (Paraguay)',
            'Alto Paraná',
            'Itapúa',
            'Caaguazú',
        ];

        foreach ($listado_oficinas as $v) {
            $p = new App\Oficina;
            $p->nombre = $v;
            $p->save();
        }

    }
}