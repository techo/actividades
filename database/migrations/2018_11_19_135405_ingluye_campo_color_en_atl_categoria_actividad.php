<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class IngluyeCampoColorEnAtlCategoriaActividad extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('atl_CategoriaActividad', 'color')) {
            Schema::table('atl_CategoriaActividad', function (Blueprint $table) {
                $table->string('color')->nullable()->default('#000000');
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
        if (Schema::hasColumn('atl_CategoriaActividad', 'color')) {
            Schema::table('atl_CategoriaActividad', function (Blueprint $table) {
                $table->dropColumn('color');
            });
        };
    }
}
