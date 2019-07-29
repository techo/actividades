<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class IncluyeCampoParaConfirmarEnInscripcion extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('Inscripcion', 'confirma')) {
            Schema::table('Inscripcion', function (Blueprint $table) {
                $table->integer('confirma')->default(0);
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
        if (Schema::hasColumn('Inscripcion', 'confirma')) {
            Schema::table('Inscripcion', function (Blueprint $table) {
                $table->dropColumn('confirma');
            });
        }
    }
}
