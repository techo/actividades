<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class IncluyeCampoCalculaFechasEnActividades extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('Actividad', 'calculaFecha')) {
            Schema::table('Actividad', function (Blueprint $table) {
                $table->integer('calculaFecha')->default(1);
            });
        };
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('Actividad', 'calculaFecha')) {
            Schema::table('Actividad', function (Blueprint $table) {
                $table->dropColumn('calculaFecha');
            });
        }
    }
}