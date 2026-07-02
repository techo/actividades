<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStripeReceiptUrlToDonationsTable extends Migration
{
    public function up()
    {
        Schema::table('donations', function (Blueprint $table) {
            // URL del recibo de Stripe (vive en el charge). Se persiste cuando
            // llega el webhook payment_intent.succeeded para no consultar Stripe
            // en cada lectura del historial.
            $table->string('stripe_receipt_url', 500)->nullable()->after('paid_at');
        });
    }

    public function down()
    {
        Schema::table('donations', function (Blueprint $table) {
            $table->dropColumn('stripe_receipt_url');
        });
    }
}
