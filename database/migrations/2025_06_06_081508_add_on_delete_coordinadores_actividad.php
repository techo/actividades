<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOnDeleteCoordinadoresActividad extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Coordinadores', function (Blueprint $table) {
            $table->foreign('idPersona', 'fk_coordinadores_persona')
                ->references('idPersona')->on('Persona')
                ->onDelete('CASCADE');
        });
    }

    public function down()
    {
        Schema::table('Coordinadores', function (Blueprint $table) {
            $table->dropForeign('fk_coordinadores_persona');
        });
    }
}
