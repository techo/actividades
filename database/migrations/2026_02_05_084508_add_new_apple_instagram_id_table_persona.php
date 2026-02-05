<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNewAppleInstagramIdTablePersona extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         Schema::table('Persona', function (Blueprint $table) {
            $table->string('apple_id')->nullable();
            $table->string('instagram_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Persona', function (Blueprint $table) {
            $table->dropColumn('apple_id');
            $table->dropColumn('instagram_id');
        });
    }
}
