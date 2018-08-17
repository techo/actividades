<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class IncluyeCampoUnsubscribeTokenEnPersona extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('Persona', 'unsubscribe_token')) {
            Schema::table('Persona', function (Blueprint $table) {
                $table->uuid('unsubscribe_token')->nullable();
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
        if (Schema::hasColumn('Persona', 'unsubscribe_token')) {
            Schema::table('Persona', function (Blueprint $table) {
                $table->dropColumn('unsubscribe_token');
            });
        }
    }
}
