<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class IncluyePagoMinimoYMaximoEnActividad extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('Actividad', 'montoMin')) {
            Schema::table('Actividad', function (Blueprint $table) {
                $table->decimal('montoMin', 8, 2)->default(0);
                $table->decimal('montoMax', 8, 2)->default(0);
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Actividad', function (Blueprint $table) {
            $table->dropColumn(['montoMin', 'montoMax']);
        });
    }
}
