<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class IncluyeCamposPagoYConfirmarEnActividad extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Actividad', function (Blueprint $table) {
            $table->integer('confirmacion')->default(0);
            $table->integer('pago')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Actividad', function (Blueprint $table) {
                $table->dropColumn('confirmacion');
                $table->dropColumn('pago');
        });
    }
}
