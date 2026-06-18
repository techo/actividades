<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DonationSubscription extends Model
{
    protected $table = 'donation_subscriptions';

    protected $fillable = [
        'person_id',
        'stripe_subscription_id',
        'stripe_customer_id',
        'amount',
        'currency',
        'interval',
        'status',
        'source',
        'country_code',
        'idempotency_key',
        'stripe_event_id',
        'current_period_end',
        'cancel_at_period_end',
        'canceled_at',
        'metadata',
    ];

    protected $casts = [
        'amount'               => 'integer',
        'current_period_end'   => 'datetime',
        'cancel_at_period_end' => 'boolean',
        'canceled_at'          => 'datetime',
        'metadata'             => 'array',
    ];

    // ── Possible status values (mirrors Stripe subscription statuses) ─────────
    const STATUS_INCOMPLETE         = 'incomplete';
    const STATUS_INCOMPLETE_EXPIRED = 'incomplete_expired';
    const STATUS_ACTIVE             = 'active';
    const STATUS_PAST_DUE           = 'past_due';
    const STATUS_CANCELED           = 'canceled';
    const STATUS_UNPAID             = 'unpaid';

    /** Terminal states — must not be overwritten by later webhook events. */
    const TERMINAL_STATUSES = [
        self::STATUS_INCOMPLETE_EXPIRED,
        self::STATUS_CANCELED,
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function persona()
    {
        return $this->belongsTo(Persona::class, 'person_id', 'idPersona');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function isTerminal(): bool
    {
        return in_array($this->status, self::TERMINAL_STATUSES, true);
    }

    /**
     * Safe status transition — no-ops if the record is already terminal.
     * Prevents out-of-order webhooks from corrupting data.
     */
    public function transitionTo(string $newStatus, array $extra = []): bool
    {
        if ($this->isTerminal()) {
            return false;
        }

        return $this->update(array_merge(['status' => $newStatus], $extra));
    }
}
