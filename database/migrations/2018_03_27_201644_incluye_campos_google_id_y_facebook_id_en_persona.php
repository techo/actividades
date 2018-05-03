<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class IncluyeCamposGoogleIdYFacebookIdEnPersona extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('Persona', 'google_id')) {
            Schema::table('Persona', function (Blueprint $table) {
                $table->string('google_id',100)->nullable();
            });
        }
        if (!Schema::hasColumn('Persona', 'facebook_id')) {
            Schema::table('Persona', function (Blueprint $table) {
                $table->string('facebook_id',100)->nullable();
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
        if (Schema::hasColumn('Persona', 'google_id')) {
            Schema::table('Persona', function (Blueprint $table) {
                $table->dropColumn(['google_id']);
            });
        }
        if (Schema::hasColumn('Persona', 'facebook_id')) {
            Schema::table('Persona', function (Blueprint $table) {
                $table->dropColumn(['facebook_id']);
            });
        }
    }
}
