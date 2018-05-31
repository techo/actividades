<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class IncluyeCampoImagenEnTipo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('Tipo', 'imagen')) {
            Schema::table('Tipo', function (Blueprint $table) {
                $table->string('imagen')->default('/img/tarjeta-1.jpg');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Tipo', function (Blueprint $table) {
            $table->dropColumn('imagen');
        });
    }
}
