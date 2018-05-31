<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AgregaCampoIdPersonaCreacionAActividad extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('Actividad', 'idPersonaCreacion')) {
            Schema::table('Actividad', function (Blueprint $table) {
                $table->integer('idPersonaCreacion')->unsigned()->nullable()->after('inscripcionInterna');
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
        Schema::table('Actividad', function (Blueprint $table) {
            $table->dropColumn('idPersonaCreacion');
        });
    }
}
