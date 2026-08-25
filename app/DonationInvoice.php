<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * One row per successfully paid Stripe subscription invoice.
 *
 * This is the payment ledger for recurring donations. Unlike
 * DonationSubscription (which holds only the current state of a subscription),
 * every monthly charge lands here, so lifetime totals and month streaks are
 * plain SQL rather than a Stripe API round-trip.
 *
 * See migration 2026_08_25_000001_create_donation_invoices_table for the why.
 */
class DonationInvoice extends Model
{
    protected $table = 'donation_invoices';

    protected $fillable = [
        'person_id',
        'donation_subscription_id',
        'stripe_invoice_id',
        'stripe_subscription_id',
        'stripe_payment_intent_id',
        'stripe_charge_id',
        'stripe_event_id',
        'amount_paid',
        'currency',
        'period_start',
        'period_end',
        'paid_at',
        'hosted_invoice_url',
        'invoice_pdf',
        'metadata',
    ];

    protected $casts = [
        'amount_paid'   => 'integer',
        'period_start'  => 'datetime',
        'period_end'    => 'datetime',
        'paid_at'       => 'datetime',
        'metadata'      => 'array',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function persona()
    {
        return $this->belongsTo(Persona::class, 'person_id', 'idPersona');
    }

    public function subscription()
    {
        return $this->belongsTo(DonationSubscription::class, 'donation_subscription_id');
    }
}
