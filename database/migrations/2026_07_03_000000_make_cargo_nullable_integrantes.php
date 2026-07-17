<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MakeCargoNullableIntegrantes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Integrantes', function (Blueprint $table) {
            $table->string('cargo')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Integrantes', function (Blueprint $table) {
            $table->string('cargo')->nullable(false)->change();
        });
    }
}
