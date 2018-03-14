<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Pais;

class AgregaArgentinaAAtlPais extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('atl_pais')) {
            DB::statement("INSERT  INTO `atl_pais` (`id`, `nombre`) VALUES(1, 'Argentina');");
        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("DELETE FROM `atl_pais` WHERE `nombre` = 'Argentina';");
    }
}
