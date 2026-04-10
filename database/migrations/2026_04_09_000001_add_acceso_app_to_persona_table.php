<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAccesoAppToPersonaTable extends Migration
{
    public function up()
    {
        Schema::table('Persona', function (Blueprint $table) {
            $table->timestamp('primer_acceso_app')->nullable()->after('estadoPersona');
            $table->timestamp('ultimo_acceso_app')->nullable()->after('primer_acceso_app');
        });
    }

    public function down()
    {
        Schema::table('Persona', function (Blueprint $table) {
            $table->dropColumn(['primer_acceso_app', 'ultimo_acceso_app']);
        });
    }
}
