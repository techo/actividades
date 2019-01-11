<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class LocalidadNoObligatoria extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Actividad', function (Blueprint $table) {
            $table->string('idLocalidad')->nullable()->change();
        });

        Schema::table('PuntoEncuentro', function (Blueprint $table) {
            $table->string('idLocalidad')->nullable()->change();
        });
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        App\Actividad::where('idLocalidad', null)
          ->update(['idLocalidad' => 0]);

        App\PuntoEncuentro::where('idLocalidad', null)
          ->update(['idLocalidad' => 0]);

        Schema::table('Actividad', function (Blueprint $table) {
            $table->string('idLocalidad')->nullable(false)->change();
        });

        Schema::table('PuntoEncuentro', function (Blueprint $table) {
            $table->string('idLocalidad')->nullable(false)->change();
        });
    }
}
