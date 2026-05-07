<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Donation extends Model
{
    protected $table = 'donations';

    protected $fillable = [
        'person_id',
        'stripe_payment_intent_id',
        'amount',
        'currency',
        'status',
        'source',
        'country_code',
        'idempotency_key',
        'stripe_event_id',
        'paid_at',
        'metadata',
    ];

    protected $casts = [
        'amount'   => 'integer',
        'paid_at'  => 'datetime',
        'metadata' => 'array',
    ];

    // ── Possible status values ────────────────────────────────────────────
    const STATUS_PENDING   = 'pending';
    const STATUS_SUCCEEDED = 'succeeded';
    const STATUS_FAILED    = 'failed';
    const STATUS_CANCELED  = 'canceled';

    /** Terminal states that must not be overwritten by later events. */
    const TERMINAL_STATUSES = [self::STATUS_SUCCEEDED, self::STATUS_CANCELED];

    // ── Relationships ─────────────────────────────────────────────────────

    /**
     * The person who made the donation.
     * Uses the Persona model's non-standard primary key (idPersona).
     */
    public function persona()
    {
        return $this->belongsTo(Persona::class, 'person_id', 'idPersona');
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    public function isTerminal(): bool
    {
        return in_array($this->status, self::TERMINAL_STATUSES, true);
    }

    /**
     * Safe status transition — ignores the update if the row is already in a
     * terminal state (prevents out-of-order webhook processing from corrupting data).
     */
    public function transitionTo(string $newStatus, array $extra = []): bool
    {
        if ($this->isTerminal()) {
            return false;
        }

        $attributes = array_merge(['status' => $newStatus], $extra);
        return $this->update($attributes);
    }
}
