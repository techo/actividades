<?php

namespace App\Services;

use Stripe\Exception\ApiErrorException;
use Stripe\PaymentIntent;
use Stripe\Stripe;
use Stripe\Webhook;

/**
 * Thin wrapper around the Stripe SDK for the generic donation flow.
 *
 * This service is intentionally separate from the per-country enrollment
 * payment logic (StripeController + Pais.config_pago). It reads its
 * credentials from config('services.stripe_donations').
 */
class StripePaymentService
{
    /** Currencies accepted by this endpoint (ISO 4217 lowercase). */
    const ALLOWED_CURRENCIES = [
        'usd', 'eur', 'brl', 'ars', 'mxn', 'clp', 'cop', 'pen', 'uyu',
        'cad', 'gbp', 'aud',
    ];

    /** Minimum donation in minor units (1.00 of any currency). */
    const AMOUNT_MIN = 100;

    /** Maximum donation in minor units (10 000.00 of any currency). */
    const AMOUNT_MAX = 1000000;

    /** Valid values for the `source` field. */
    const ALLOWED_SOURCES = ['login_us', 'home_pill', 'profile'];

    // ─────────────────────────────────────────────────────────────────────

    public function __construct()
    {
        // Key validation is deferred to the first actual Stripe call.
        // This prevents DI/boot failures when .env is not yet configured.
    }

    /**
     * Bootstrap the Stripe SDK with the donations key.
     * Called lazily before any SDK operation.
     *
     * @throws \RuntimeException if STRIPE_DONATIONS_SECRET is not set
     */
    private function boot(): void
    {
        $secret = config('services.stripe_donations.secret');

        if (empty($secret)) {
            throw new \RuntimeException(
                'STRIPE_DONATIONS_SECRET is not configured. ' .
                'Add it to .env and run `php artisan config:clear`.'
            );
        }

        Stripe::setApiKey($secret);
    }

    /**
     * Create a Stripe PaymentIntent.
     *
     * @param  int    $amount         Minor units (e.g. 1500 = 15.00)
     * @param  string $currency       ISO 4217 lowercase
     * @param  int    $personId       Persona.idPersona
     * @param  string $source         Where the user initiated the donation
     * @param  string $idempotencyKey Unique key to prevent duplicate intents
     * @param  array  $extra          Optional extra metadata stored on Stripe
     * @return PaymentIntent
     * @throws ApiErrorException
     */
    public function createPaymentIntent(
        int $amount,
        string $currency,
        int $personId,
        string $source,
        string $idempotencyKey,
        array $extra = []
    ): PaymentIntent {
        $this->boot();

        $params = [
            'amount'               => $amount,
            'currency'             => $currency,
            'payment_method_types' => ['card'],
            'metadata'             => array_merge([
                'person_id' => $personId,
                'source'    => $source,
            ], $extra),
        ];

        return PaymentIntent::create(
            $params,
            ['idempotency_key' => $idempotencyKey]
        );
    }

    /**
     * Validate and parse a Stripe webhook payload.
     *
     * @param  string $rawPayload  Raw request body (do NOT decode first)
     * @param  string $sigHeader   Value of the Stripe-Signature header
     * @return \Stripe\Event
     * @throws \Stripe\Exception\SignatureVerificationException
     */
    public function constructWebhookEvent(string $rawPayload, string $sigHeader): \Stripe\Event
    {
        $this->boot();

        $webhookSecret = config('services.stripe_donations.webhook_secret');

        if (empty($webhookSecret)) {
            throw new \RuntimeException(
                'STRIPE_DONATIONS_WEBHOOK_SECRET is not configured.'
            );
        }

        return Webhook::constructEvent($rawPayload, $sigHeader, $webhookSecret);
    }

    // ── Static helpers ────────────────────────────────────────────────────

    /**
     * Map a Stripe PaymentIntent status to our internal normalised status.
     * Note: 'failed' is only set via webhook events, never from PI status.
     */
    public static function normalizeStatus(string $stripeStatus): string
    {
        switch ($stripeStatus) {
            case 'succeeded':
                return 'succeeded';
            case 'canceled':
                return 'canceled';
            default:
                // requires_payment_method | requires_confirmation |
                // requires_action | processing | requires_capture
                return 'pending';
        }
    }
}
