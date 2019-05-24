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
        $this->call(RolesTableSeeder::class);
        $this->call(PermissionsTableSeeder::class);
        $this->call(RolePermissionsSeeder::class);

        $this->call(ActividadesSeeder::class);

        $this->call(UsuarioAdminSeeder::class);
        $this->call(UsuarioCoordinadorSeeder::class);
    }
}
