<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCoordinadoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Coordinadores', function (Blueprint $table) {
            $table->increments('idCoordinador');
            $table->integer('idPersona')->nullable()->index('fk_Coordinadores_2_idx');
            $table->integer('idActividad')->nullable()->index('fk_Coordinadores_3_idx');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Coordinadores');
    }
}
