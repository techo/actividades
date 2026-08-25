<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ledger of individual successful subscription charges (one row per paid
 * Stripe invoice).
 *
 * Why this table exists:
 *   `donation_subscriptions` holds ONE row per subscription and only ever
 *   reflects the *current* state (amount, status, period end). Each monthly
 *   charge was never persisted, so lifetime totals and consecutive-month
 *   streaks for recurring donors were not derivable from local data — they
 *   required calling the Stripe Invoices API on every read.
 *
 *   This table records each `invoice.paid` event, making both
 *   `SUM(amount_paid)` (total aportado) and a paid-month streak computable
 *   with plain SQL. It also serves as an auditable payment ledger the system
 *   previously lacked.
 *
 * Idempotency: `stripe_invoice_id` is unique — the webhook upserts on it, so
 * Stripe retries / duplicate deliveries never double-count a charge.
 *
 * Note: one-time donations already live in `donations`; this table is only
 * for recurring subscription invoices.
 */
class CreateDonationInvoicesTable extends Migration
{
    public function up()
    {
        Schema::create('donation_invoices', function (Blueprint $table) {
            $table->bigIncrements('id');

            // ── Who ──────────────────────────────────────────────────────────
            // Denormalized from the subscription so per-person aggregation
            // (lifetime total, streak) is a single indexed query.
            $table->unsignedBigInteger('person_id');
            $table->index('person_id');

            $table->unsignedBigInteger('donation_subscription_id')->nullable();
            $table->index('donation_subscription_id');

            // ── Stripe IDs ────────────────────────────────────────────────────
            $table->string('stripe_invoice_id')->unique();      // idempotency key
            $table->string('stripe_subscription_id')->nullable();
            $table->index('stripe_subscription_id');
            $table->string('stripe_payment_intent_id')->nullable();
            $table->string('stripe_charge_id')->nullable();
            $table->string('stripe_event_id')->nullable();

            // ── Amount actually collected ─────────────────────────────────────
            $table->unsignedInteger('amount_paid');  // minor units (e.g. 1000 = 10.00)
            $table->string('currency', 3);           // ISO 4217 lowercase

            // ── Billing period this invoice covers ────────────────────────────
            $table->timestamp('period_start')->nullable();
            $table->timestamp('period_end')->nullable();
            $table->timestamp('paid_at')->nullable();

            // ── Receipt / audit links ─────────────────────────────────────────
            $table->string('hosted_invoice_url')->nullable();
            $table->string('invoice_pdf')->nullable();

            // ── Flexible metadata ─────────────────────────────────────────────
            $table->json('metadata')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('donation_invoices');
    }
}
