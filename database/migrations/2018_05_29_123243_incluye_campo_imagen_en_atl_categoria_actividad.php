<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class IncluyeCampoImagenEnAtlCategoriaActividad extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('atl_CategoriaActividad', 'imagen')) {
            Schema::table('atl_CategoriaActividad', function (Blueprint $table) {
                $table->string('imagen')->nullable();
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
        if (Schema::hasColumn('atl_CategoriaActividad', 'imagen')) {
            Schema::table('atl_CategoriaActividad', function (Blueprint $table) {
                $table->dropColumn('imagen');
            });
        };
    }
}