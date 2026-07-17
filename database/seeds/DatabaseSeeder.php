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

        // Países y headers ANTES que los factories: los países reales usan IDs
        // fijos (13 = Argentina = APP_PAIS_DEFAULT); sin ellos y sin el header
        // del país activo, el sistema no arranca (500 en toda vista pública).
        $this->call(PaisesSeeder::class);
        $this->call(PaisesHabilitadosSeeder::class);
        $this->call(HomeHeadersSeeder::class);

        // Oficinas antes que actividades: el factory de Actividad usa idOficina = 1.
        $this->call(OficinasSeeder::class);

        $this->call(ActividadesSeeder::class);

        $this->call(UsuarioAdminSeeder::class);
        $this->call(UsuarioCoordinadorSeeder::class);
    }
}
