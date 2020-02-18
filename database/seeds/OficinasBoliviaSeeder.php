<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProvinciasBolviaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $listado_oficinas = [
            'Oficina Nacional Bolivia',
        ];

        foreach ($listado_oficinas as $v) {
            $p = new App\Oficina;
            $p->nombre = $v;
            $p->save();
        }

    }
}