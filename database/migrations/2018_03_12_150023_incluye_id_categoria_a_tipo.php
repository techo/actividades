<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class IncluyeIdCategoriaATipo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('Tipo', 'idCategoria')) {
            //
            Schema::table('Tipo', function (Blueprint $table) {
                $table->integer('idCategoria')->unsigned()->nullable();
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
        Schema::disableForeignKeyConstraints();
        DB::table('Tipo')->truncate();
        Schema::table('Tipo', function (Blueprint $table) {
            $table->dropColumn(['idCategoria']);
        });
    }
}
