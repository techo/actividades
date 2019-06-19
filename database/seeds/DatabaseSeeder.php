<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(PermisosSeeder::class);

        $this->call(ActividadesSeeder::class);

        $this->call(UsuarioAdminSeeder::class);
        $this->call(UsuarioCoordinadorSeeder::class);
    }
}
