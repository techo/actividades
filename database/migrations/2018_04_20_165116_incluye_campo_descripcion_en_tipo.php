<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class IncluyeCampoDescripcionEnTipo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('Tipo', 'descripcion')) {
            Schema::table('Tipo', function (Blueprint $table) {
                // Nullable para que sea retro compatible con pilote
                $table->text('descripcion')->nullable();
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
        Schema::table('Tipo', function (Blueprint $table) {
            $table->dropColumn('descripcion');
        });
    }
}
