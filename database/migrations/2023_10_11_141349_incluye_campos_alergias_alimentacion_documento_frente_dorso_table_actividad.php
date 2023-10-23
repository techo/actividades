<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class IncluyeCamposAlergiasAlimentacionDocumentoFrenteDorsoTableActividad extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ficha_medicas', function (Blueprint $table) {
            $table->string('alergias')->nullable();
            $table->string('alimentacion')->nullable();
            $table->string('documento_frente')->nullable();
            $table->string('documento_dorso')->nullable();
            $table->string('cobertura_tipo',80)->nullable()->after('grupo_sanguinieo');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ficha_medicas', function (Blueprint $table) {
            $table->dropColumn('alergias');
            $table->dropColumn('alimentacion');
            $table->dropColumn('documento_frente');
            $table->dropColumn('documento_dorso');
            $table->dropColumn('cobertura_tipo');
        });
    }
}
