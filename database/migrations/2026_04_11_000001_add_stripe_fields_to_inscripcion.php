<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStripeFieldsToInscripcion extends Migration
{
    public function up()
    {
        Schema::table('Inscripcion', function (Blueprint $table) {
            $table->string('stripe_session_id', 255)->nullable()->after('voucherUrl');
            $table->string('stripe_payment_intent_id', 255)->nullable()->after('stripe_session_id');
            $table->string('metodo_pago', 50)->nullable()->after('stripe_payment_intent_id');

            $table->index('stripe_session_id', 'idx_inscripcion_stripe_session');
            $table->index('stripe_payment_intent_id', 'idx_inscripcion_stripe_pi');
        });
    }

    public function down()
    {
        Schema::table('Inscripcion', function (Blueprint $table) {
            $table->dropIndex('idx_inscripcion_stripe_session');
            $table->dropIndex('idx_inscripcion_stripe_pi');
            $table->dropColumn(['stripe_session_id', 'stripe_payment_intent_id', 'metodo_pago']);
        });
    }
}
