<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldsActividadesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Actividad', function (Blueprint $table) {
            $table->json('actividades_tags')->nullable();
            $table->string('imagen_tarjeta')->nullable();
            $table->boolean('destacada')->default(false);
            $table->string('imagen_destacada')->nullable();
        });
    }

    public function down()
    {
        Schema::table('Actividad', function (Blueprint $table) {
            $table->dropColumn(['actividades_tags']);
            $table->dropColumn(['imagen_tarjeta']);
            $table->dropColumn(['destacada']);
            $table->dropColumn(['imagen_destacada']);
        });
    }
}
