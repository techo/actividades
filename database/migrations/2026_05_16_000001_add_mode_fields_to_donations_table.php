<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add mode and payment_method_type columns to the donations table.
 *
 * mode:                'one_time' (default) or 'recurring'
 * payment_method_type: 'card', 'apple_pay', 'google_pay', 'pix'
 */
class AddModeFieldsToDonationsTable extends Migration
{
    public function up()
    {
        Schema::table('donations', function (Blueprint $table) {
            $table->string('mode', 20)->default('one_time')->after('currency');
            $table->string('payment_method_type', 30)->nullable()->after('mode');
        });
    }

    public function down()
    {
        Schema::table('donations', function (Blueprint $table) {
            $table->dropColumn(['mode', 'payment_method_type']);
        });
    }
}
