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
        $listado_oficinas = [
            'Oficina Nacional Bolivia',
        ];

        foreach ($listado_divisiones as $v) {
            $p = new App\Oficina;
            $p->nombre = $v;
            $p->save();
        }

    }
}