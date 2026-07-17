<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //Primero borra los datos existentes en las tablas de permisos
        DB::statement('DELETE FROM role_has_permissions'); echo 'Tabla role_has_permissions BORRADA' . PHP_EOL;
        DB::statement('DELETE FROM model_roles'); echo 'Tabla model_roles BORRADA' . PHP_EOL;
        DB::statement('DELETE FROM model_perms'); echo 'Tabla model_perms BORRADA' . PHP_EOL;
        DB::statement('DELETE FROM permissions'); echo 'Tabla permissions BORRADA' . PHP_EOL;
        DB::statement('DELETE FROM roles'); echo 'Tabla roles BORRADA' . PHP_EOL;
        $this->call(RolesTableSeeder::class);
        $this->call(PermissionsTableSeeder::class);
        $this->call(RolePermissionsSeeder::class);

        // Países y headers ANTES que los factories: los países reales usan IDs
        // fijos (13 = Argentina = APP_PAIS_DEFAULT) y si un factory crea países
        // fake primero, el autoincrement pisa esos IDs y el sistema no arranca.
        $this->call(PaisesSeeder::class);
        $this->call(PaisesHabilitadosSeeder::class);
        $this->call(HomeHeadersSeeder::class);

        $this->call(ActividadesSeeder::class);
        $this->call(ActividadesConPagoSeeder::class);
        $this->call(ActividadesSinLocalidadSeeder::class);

        $this->call(UsuarioAdminSeeder::class);
        $this->call(UsuarioCoordinadorSeeder::class);

        $this->call(UnsubscribeTokenSeeder::class);
    }
}
