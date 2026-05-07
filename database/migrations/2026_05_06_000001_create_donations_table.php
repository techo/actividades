<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDonationsTable extends Migration
{
    public function up()
    {
        Schema::create('donations', function (Blueprint $table) {
            $table->bigIncrements('id');

            // Who donated — references Persona.idPersona (no FK constraint for
            // compatibility with the legacy schema that avoids strict FK chains).
            $table->unsignedBigInteger('person_id');

            // Stripe identifiers
            $table->string('stripe_payment_intent_id', 255)->unique();

            // Payment details (amount in minor units, e.g. 1500 = 15.00)
            $table->unsignedInteger('amount');
            $table->string('currency', 3);

            // Normalised status: pending | succeeded | failed | canceled
            $table->string('status', 20)->default('pending');

            // Where the user initiated the donation (login_us | home_pill | profile | ...)
            $table->string('source', 50)->nullable();

            // Optional geo hint supplied by the mobile app
            $table->string('country_code', 10)->nullable();

            // Client-supplied (or server-generated) idempotency key.
            // Prevents duplicate PaymentIntents on mobile retries.
            $table->string('idempotency_key', 255)->nullable();

            // ID of the last Stripe webhook event that mutated this row.
            // Used to skip re-processing the same event on Stripe retries.
            $table->string('stripe_event_id', 255)->nullable();

            // Set when status transitions to 'succeeded'
            $table->timestamp('paid_at')->nullable();

            // Flexible JSON bag — raw Stripe PI metadata, future activity linkage, etc.
            $table->json('metadata')->nullable();

            $table->timestamps();

            // ── Indexes ──────────────────────────────────────────────────────
            $table->index('person_id');
            $table->index('idempotency_key');
            $table->index('status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('donations');
    }
}
