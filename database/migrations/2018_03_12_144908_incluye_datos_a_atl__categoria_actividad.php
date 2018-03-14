<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class IncluyeDatosAAtlCategoriaActividad extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('atl_CategoriaActividad')) {
            DB::statement("INSERT INTO `atl_CategoriaActividad` (`id`, `nombre`, `descripcion`)
                VALUES
                    (1,'Actividades en Asentamientos','Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.'),
                    (2,'Actividades en Oficina','Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.'),
                    (3,'Eventos Especiales','Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.');
            ");
        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('atl_CategoriaActividad')->truncate();
        DB::statement("ALTER TABLE atl_CategoriaActividad AUTO_INCREMENT = 1;");
    }
}
