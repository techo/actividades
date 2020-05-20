<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class IncluyeCampoLinkpagoEnActividad extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('Actividad', 'linkPago')) {
            Schema::table('Actividad', function (Blueprint $table) {
                // Nullable para que sea retro compatible con pilote
                $table->string('linkPago')->nullable();
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
        if (Schema::hasColumn('Actividad', 'linkPago')) {
            Schema::table('Actividad', function (Blueprint $table) {
                $table->dropColumn('linkPago');
            });
        }
    }
}
