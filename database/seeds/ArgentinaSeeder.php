<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ArgentinaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(ProvinciasArgentinaSeeder::class);
        $this->call(LocalidadesArgentinaSeeder::class);
        $this->call(OficinasArgentinaSeeder::class);
    }
}
