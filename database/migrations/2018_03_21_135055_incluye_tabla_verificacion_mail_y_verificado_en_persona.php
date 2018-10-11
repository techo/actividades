<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class IncluyeTablaVerificacionMailYVerificadoEnPersona extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('VerificacionMailPersona')) {
            Schema::create('VerificacionMailPersona', function (Blueprint $table) {
                $table->integer('idPersona');
                $table->string('token');
                $table->timestamps();
            });
        }
        Schema::table('Persona', function (Blueprint $table) {
            $table->boolean('verificado')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('VerificacionMailPersona');
        if (Schema::hasColumn('Persona', 'verificado')) {
            Schema::table('Persona', function (Blueprint $table) {
                $table->dropColumn(['verificado']);
            });
        }
    }
}
