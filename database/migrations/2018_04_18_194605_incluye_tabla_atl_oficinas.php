<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class IncluyeTablaAtlOficinas extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('atl_oficinas')) {
            Schema::create('atl_oficinas', function (Blueprint $table) {
                $table->increments('id');
                $table->string('nombre');
            });
        }

        DB::table('atl_oficinas')->insert([
            ['nombre' => 'Nacional Argentina'],
            ['nombre' => 'Regional Buenos Aires'],
            ['nombre' => 'Buenos Aires Norte'],
            ['nombre' => 'Buenos Aires Sur'],
            ['nombre' => 'Buenos Aires Oeste'],
            ['nombre' => 'Buenos Aires La Plata'],
            ['nombre' => 'Neuquén - Río Negro'],
            ['nombre' => 'Córdoba Capital'],
            ['nombre' => 'Río Cuarto'],
            ['nombre' => 'Rosario'],
            ['nombre' => 'Posadas'],
            ['nombre' => 'Oberá'],
            ['nombre' => 'Corrientes - Chaco'],
            ['nombre' => 'Resistencia'],
            ['nombre' => 'Salta'],
            ['nombre' => 'Tucumán']
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('atl_oficinas');
    }
}
