<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class IncluyeCampoAceptaMarketingEnPersona extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('Persona', 'acepta_marketing')) {
            Schema::table('Persona', function (Blueprint $table) {
                $table->boolean('acepta_marketing')->nullable();
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
        if (Schema::hasColumn('Persona', 'acepta_marketing')) {
            Schema::table('Persona', function (Blueprint $table) {
                $table->dropColumn('acepta_marketing');
            });
        }
    }
}
