<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FlujoActividadesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('Tipo')
            ->where('nombre', 'Construcción')
            ->update(['flujo' => 'CONSTRUCCION']);
    }
}