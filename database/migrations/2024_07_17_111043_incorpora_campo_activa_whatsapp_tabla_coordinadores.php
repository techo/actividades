<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class IncorporaCampoActivaWhatsappTablaCoordinadores extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Coordinadores', function (Blueprint $table) {
            $table->boolean('activaWhatsapp')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Coordinadores', function (Blueprint $table) {
            $table->dropColumn('activaWhatsapp');
        });
    }
}
