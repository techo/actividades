<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $this->call(PaisesSeeder::class);
        $this->call(CategoriaActividadesSeeder::class);
        $this->call(TiposSeeder::class);
    
    }
}