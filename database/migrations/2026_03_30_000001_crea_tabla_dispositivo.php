<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreaTablaDispositivo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Dispositivo', function (Blueprint $table) {
            $table->increments('idDispositivo');
            $table->integer('idPersona')->index();
            $table->string('player_id', 255)->unique();   // OneSignal player_id / subscription_id
            $table->string('plataforma', 20)->nullable(); // 'ios', 'android'
            $table->boolean('activo')->default(true);     // false al hacer logout en la app
            $table->timestamps();

            $table->foreign('idPersona')
                  ->references('idPersona')
                  ->on('Persona')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Dispositivo');
    }
}
