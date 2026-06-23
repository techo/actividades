<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDonationPresetsTable extends Migration
{
    public function up()
    {
        Schema::create('donation_presets', function (Blueprint $table) {
            $table->bigIncrements('id');

            // País al que aplican los montos — referencia atl_pais.id (= Persona.idPais).
            // Sin FK constraint, en línea con el resto del esquema (atl_pais es legacy).
            // Una sola fila por país.
            $table->unsignedInteger('id_pais')->unique();

            // Moneda local en la que se cobra (ISO 4217 minúscula, ej. 'mxn').
            $table->string('currency', 3);

            // Tres montos sugeridos, en UNIDAD MAYOR (ej. 34 = $34.00).
            // Se guardan "lindos" para que quien edita en BD vea 34 y no 3400.
            // El cliente convierte a unidad menor con minor_unit_exponent antes de Stripe.
            $table->unsignedInteger('preset_low');
            $table->unsignedInteger('preset_mid');
            $table->unsignedInteger('preset_high');

            // Cuántos decimales tiene la moneda (2 para la mayoría, 0 para CLP/PYG…).
            $table->unsignedTinyInteger('minor_unit_exponent')->default(2);

            // Si el país ofrece PIX como medio de pago (hoy solo Brasil).
            $table->boolean('pix_enabled')->default(false);

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('donation_presets');
    }
}
