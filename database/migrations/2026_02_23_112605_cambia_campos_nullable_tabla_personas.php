<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CambiaCamposNullableTablaPersonas extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Persona', function (Blueprint $table) {
            $table->string('dni')->nullable()->change();
            $table->date('fechaNacimiento')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('Persona', function (Blueprint $table) {
            $table->string('dni')->nullable(false)->change();
            $table->date('fechaNacimiento')->nullable(false)->change();
        });
    }
}