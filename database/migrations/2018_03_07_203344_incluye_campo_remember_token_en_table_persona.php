<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class IncluyeCampoRememberTokenEnTablePersona extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('Persona', 'remember_token')) {
            //
            Schema::table('Persona', function (Blueprint $table) {
                $table->string('remember_token', 100);
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
        if (Schema::hasColumn('persona', 'remember_token')) {
            //
            Schema::table('persona', function (Blueprint $table) {
                $table->dropColumn('remember_token');
            });
        }

    }
}
