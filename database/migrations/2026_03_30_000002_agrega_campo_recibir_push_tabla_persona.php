<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AgregaCampoRecibirPushTablaPersona extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Persona', function (Blueprint $table) {
            // Mismo patrón que recibirMails — el usuario puede optar por no recibir push
            // sin que se borren sus dispositivos registrados
            $table->boolean('recibir_push')->default(true)->after('recibirMails');
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
            $table->dropColumn('recibir_push');
        });
    }
}
