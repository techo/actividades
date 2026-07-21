<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Permite rechazar el documento de identidad (frente/dorso) de la ficha
 * médica, análogo al rechazo de comprobante de pago. La ficha médica es por
 * persona, así que el rechazo aplica a la persona en todas sus actividades.
 * Un único flag para ambas caras del documento.
 */
class AddDocumentoRechazoToFichaMedicas extends Migration
{
    public function up()
    {
        Schema::table('ficha_medicas', function (Blueprint $table) {
            $table->boolean('documento_rechazado')->default(false);
            $table->text('documento_rechazo_motivo')->nullable();
        });
    }

    public function down()
    {
        Schema::table('ficha_medicas', function (Blueprint $table) {
            $table->dropColumn(['documento_rechazado', 'documento_rechazo_motivo']);
        });
    }
}
