<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Maps a Persona to a Stripe Customer ID for the donations flow.
 * One row per person — prevents duplicate Stripe customers.
 */
class StripeCustomer extends Model
{
    protected $table = 'stripe_customers';

    protected $fillable = [
        'person_id',
        'stripe_customer_id',
    ];

    public function persona()
    {
        return $this->belongsTo(Persona::class, 'person_id', 'idPersona');
    }
}
