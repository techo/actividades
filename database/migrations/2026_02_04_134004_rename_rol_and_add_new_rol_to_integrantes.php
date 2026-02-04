<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameRolAndAddNewRolToIntegrantes extends Migration
{
    public function up()
    {
        Schema::table('Integrantes', function (Blueprint $table) {

            // 1. Renombrar la columna existente
            $table->renameColumn('rol', 'cargo');
        });
    }

    public function down()
    {
        Schema::table('Integrantes', function (Blueprint $table) {

            // 1. Eliminar la nueva columna "rol"
            $table->renameColumn('cargo', 'rol');
        });
    }
}
