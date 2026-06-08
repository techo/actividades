<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Links a one-time donation to an enrollment when the payment is made
 * as part of an activity registration (mobile Stripe flow).
 * Nullable — free donations unrelated to any activity keep it null.
 */
class AddInscripcionIdToDonationsTable extends Migration
{
    public function up()
    {
        Schema::table('donations', function (Blueprint $table) {
            $table->unsignedBigInteger('inscripcion_id')->nullable()->after('person_id');
            $table->index('inscripcion_id');
        });
    }

    public function down()
    {
        Schema::table('donations', function (Blueprint $table) {
            $table->dropIndex(['inscripcion_id']);
            $table->dropColumn('inscripcion_id');
        });
    }
}
