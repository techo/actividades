<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Stores recurring donation subscriptions backed by Stripe Subscriptions.
 *
 * Stripe subscription lifecycle:
 *   incomplete → active → past_due → canceled / unpaid
 *   incomplete_expired (if first invoice is never paid within ~23 h)
 *
 * The client_secret for the first payment comes from:
 *   subscription.latest_invoice.payment_intent.client_secret
 * Subsequent payments are handled automatically by Stripe + webhooks.
 */
class CreateDonationSubscriptionsTable extends Migration
{
    public function up()
    {
        Schema::create('donation_subscriptions', function (Blueprint $table) {
            $table->bigIncrements('id');

            // ── Who ──────────────────────────────────────────────────────────
            $table->unsignedBigInteger('person_id');
            $table->index('person_id');

            // ── Stripe IDs ────────────────────────────────────────────────────
            $table->string('stripe_subscription_id')->unique();
            $table->string('stripe_customer_id');

            // ── Payment details ───────────────────────────────────────────────
            $table->unsignedInteger('amount');      // minor units (e.g. 1000 = 10.00)
            $table->string('currency', 3);          // ISO 4217 lowercase
            $table->string('interval', 10);         // 'month' | 'year'

            // ── Status ────────────────────────────────────────────────────────
            $table->string('status', 30)->default('incomplete');
            $table->index('status');

            // ── Context ───────────────────────────────────────────────────────
            $table->string('source', 50)->nullable();
            $table->string('country_code', 10)->nullable();

            // ── Idempotency ───────────────────────────────────────────────────
            $table->string('idempotency_key', 255)->nullable();
            $table->index('idempotency_key');

            // ── Webhook tracking ──────────────────────────────────────────────
            $table->string('stripe_event_id')->nullable();

            // ── Billing period ────────────────────────────────────────────────
            $table->timestamp('current_period_end')->nullable();
            $table->timestamp('canceled_at')->nullable();

            // ── Flexible metadata ─────────────────────────────────────────────
            $table->json('metadata')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('donation_subscriptions');
    }
}
