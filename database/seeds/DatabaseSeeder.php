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
        //Primero borra los datos existentes en las tablas de permisos
         DB::statement('DELETE FROM role_has_permissions'); echo 'Tabla role_has_permissions BORRADA' . PHP_EOL;
         DB::statement('DELETE FROM model_roles'); echo 'Tabla model_roles BORRADA' . PHP_EOL;
         DB::statement('DELETE FROM model_perms'); echo 'Tabla model_perms BORRADA' . PHP_EOL;
         DB::statement('DELETE FROM permissions'); echo 'Tabla permissions BORRADA' . PHP_EOL;
         DB::statement('DELETE FROM roles'); echo 'Tabla roles BORRADA' . PHP_EOL;
         $this->call(RolesTableSeeder::class);
         $this->call(PermissionsTableSeeder::class);
         $this->call(RolePermissionsSeeder::class);
         $this->call(ImagenesActividadesSeeder::class);
         //TODO: definir usuario Admin y asignar el rol
        $this->call(LocalidadGenericaSeed::class);
        $this->call(PaisesSeeder::class);
        $this->call(FlujoActividadesSeeder::class);
        $this->call(UnsubscribeTokenSeeder::class);
    }
}
