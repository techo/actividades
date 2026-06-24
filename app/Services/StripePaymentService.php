<?php

namespace App\Services;

use App\StripeCustomer;
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

    /** Valid payment modes. */
    const ALLOWED_MODES = ['one_time', 'recurring'];

    /** Valid payment method identifiers accepted by the API (kept for backwards compat). */
    const ALLOWED_PAYMENT_METHODS = ['card', 'apple_pay', 'google_pay', 'pix'];

    /** Valid billing intervals for recurring donations. */
    const ALLOWED_INTERVALS = ['month', 'year'];

    /**
     * Stripe API version this donation flow is written against.
     *
     * Subscription creation/retrieval rely on `latest_invoice.payment_intent`
     * (the first-payment client_secret) and `current_period_end` on the
     * subscription object. Stripe removed/relocated both in later default API
     * versions, so we pin the version on those calls to keep the response
     * shape the code expects. PaymentIntent and webhook flows are unaffected.
     */
    const PINNED_API_VERSION = '2020-08-27';

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

    // ── PaymentIntent ─────────────────────────────────────────────────────────

    /**
     * Create a Stripe PaymentIntent for a one-time donation.
     *
     * Uses `automatic_payment_methods` so Stripe enables all payment methods
     * configured in the Dashboard for this account (cards, wallets, PIX, etc.)
     * without needing explicit server-side configuration per method.
     *
     * @param  int    $amount          Minor units (e.g. 1500 = 15.00)
     * @param  string $currency        ISO 4217 lowercase
     * @param  int    $personId        Persona.idPersona
     * @param  string $source          Where the user initiated the donation
     * @param  string $idempotencyKey  Unique key to prevent duplicate intents
     * @param  string $paymentMethod   Kept for backwards compat / metadata only
     * @param  array  $extra           Optional extra metadata stored on Stripe
     * @return PaymentIntent
     * @throws ApiErrorException
     */
    public function createPaymentIntent(
        int $amount,
        string $currency,
        int $personId,
        string $source,
        string $idempotencyKey,
        string $paymentMethod = 'card',
        array $extra = [],
        string $payerName = '',
        string $payerEmail = '',
        string $payerTaxId = ''
    ): PaymentIntent {
        $this->boot();

        $metadata = array_merge([
            'person_id'      => $personId,
            'source'         => $source,
            'payment_method' => $paymentMethod,
        ], $extra);

        if ($paymentMethod === 'pix') {
            // PIX: confirm immediately server-side so Stripe generates the
            // QR code / copy-paste code in next_action.pix_display_qr_code.
            // Stripe requires billing_details.name for server-side PIX confirmation.
            // automatic_payment_methods cannot be used with server-side confirmation.
            $params = [
                'amount'               => $amount,
                'currency'             => $currency,
                'payment_method_types' => ['pix'],
                'payment_method_data'  => [
                    'type'             => 'pix',
                    'billing_details'  => array_filter([
                        'name'   => $payerName  ?: 'Donante TECHO',
                        'email'  => $payerEmail  ?: null,
                        'tax_id' => $payerTaxId ?: null,
                    ]),
                ],
                'confirm'              => true,
                'metadata'             => $metadata,
            ];
        } else {
            // Card / wallets: use automatic_payment_methods so Stripe enables
            // everything configured in the Dashboard (Apple Pay, Google Pay, Link…).
            // The client (Stripe.js / mobile SDK) completes the confirmation.
            $params = [
                'amount'                    => $amount,
                'currency'                  => $currency,
                'automatic_payment_methods' => ['enabled' => true],
                'metadata'                  => $metadata,
            ];
        }

        return PaymentIntent::create(
            $params,
            ['idempotency_key' => $idempotencyKey]
        );
    }

    /**
     * Retrieve an existing PaymentIntent by ID (with next_action expanded for PIX replays).
     *
     * @throws ApiErrorException
     */
    public function retrievePaymentIntent(string $intentId): PaymentIntent
    {
        $this->boot();
        return PaymentIntent::retrieve($intentId);
    }

    // ── Customer ──────────────────────────────────────────────────────────────

    /**
     * Get the Stripe Customer ID for a persona, creating one if needed.
     * Uses our local `stripe_customers` table to avoid duplicate customers.
     *
     * @throws ApiErrorException
     */
    public function getOrCreateCustomer(int $personId, string $email): string
    {
        $this->boot();

        $existing = StripeCustomer::where('person_id', $personId)->first();

        if ($existing) {
            return $existing->stripe_customer_id;
        }

        $customer = \Stripe\Customer::create([
            'email'    => $email,
            'metadata' => ['person_id' => $personId],
        ]);

        StripeCustomer::create([
            'person_id'          => $personId,
            'stripe_customer_id' => $customer->id,
        ]);

        return $customer->id;
    }

    // ── Subscription ──────────────────────────────────────────────────────────

    /**
     * Create a Stripe Subscription for a recurring donation.
     *
     * Uses `payment_behavior: 'default_incomplete'` so the subscription starts
     * in `incomplete` status and the app must confirm the first payment via the
     * client_secret returned from `latest_invoice.payment_intent`.
     * Subsequent charges are handled automatically by Stripe + webhooks.
     *
     * @param  string $customerId     Stripe Customer ID
     * @param  int    $amount         Minor units
     * @param  string $currency       ISO 4217 lowercase
     * @param  string $interval       'month' | 'year'
     * @param  int    $personId       Persona.idPersona (for metadata)
     * @param  string $source         Where the donation originated
     * @param  string $idempotencyKey Prevent duplicate subscriptions
     * @param  array  $extra          Optional extra metadata
     * @return \Stripe\Subscription   With latest_invoice.payment_intent expanded
     * @throws ApiErrorException
     */
    public function createSubscription(
        string $customerId,
        int $amount,
        string $currency,
        string $interval,
        int $personId,
        string $source,
        string $idempotencyKey,
        array $extra = []
    ): \Stripe\Subscription {
        $this->boot();

        // Create a Price inline first — older Stripe SDK versions (v8.x) don't support
        // product_data nested inside price_data in Subscription::create items.
        $price = \Stripe\Price::create([
            'unit_amount'  => $amount,
            'currency'     => $currency,
            'recurring'    => ['interval' => $interval],
            'product_data' => ['name' => 'TECHO Donación Recurrente'],
        ]);

        return \Stripe\Subscription::create([
            'customer' => $customerId,
            'items'    => [['price' => $price->id]],
            'payment_behavior' => 'default_incomplete',
            'payment_settings' => [
                'save_default_payment_method' => 'on_subscription',
                // No payment_method_types restriction — Stripe uses all methods
                // enabled in the Dashboard for this account (wallets, cards, etc.)
            ],
            'expand'   => ['latest_invoice.payment_intent'],
            'metadata' => array_merge([
                'person_id' => $personId,
                'source'    => $source,
            ], $extra),
        ], [
            'idempotency_key' => $idempotencyKey,
            // Pin the API version this flow was written against. Newer default
            // versions no longer expose `latest_invoice.payment_intent` (the
            // client_secret source) nor `current_period_end` on the subscription
            // object, which would make both come back null.
            'stripe_version'  => self::PINNED_API_VERSION,
        ]);
    }

    /**
     * Retrieve a Stripe Subscription by ID (with latest_invoice expanded
     * so we can re-fetch the client_secret on idempotent replays).
     *
     * @throws ApiErrorException
     */
    public function retrieveSubscription(string $subscriptionId): \Stripe\Subscription
    {
        $this->boot();

        return \Stripe\Subscription::retrieve([
            'id'     => $subscriptionId,
            'expand' => ['latest_invoice.payment_intent'],
        ], ['stripe_version' => self::PINNED_API_VERSION]);
    }

    /**
     * Change the amount and/or billing interval of an existing subscription.
     *
     * Stripe Prices are immutable, so a new Price is created and the existing
     * subscription item is repointed to it. The currency cannot change — it is
     * read from the current item's price and reused.
     *
     * `proration_behavior: 'none'` → the new amount applies from the next
     * billing cycle; nothing is charged or credited mid-period (the natural
     * behaviour for a donation).
     *
     * @param  string $subscriptionId Stripe Subscription ID (sub_...)
     * @param  int    $amount         New amount, minor units
     * @param  string $interval       'month' | 'year'
     * @param  string $idempotencyKey Prevent double-applying on retries
     * @return \Stripe\Subscription   Updated subscription
     * @throws ApiErrorException
     */
    public function updateSubscriptionAmount(
        string $subscriptionId,
        int $amount,
        string $interval,
        string $idempotencyKey
    ): \Stripe\Subscription {
        $this->boot();

        // Current subscription — needed for the item id (to replace, not add)
        // and the currency (immutable, must be reused on the new price).
        $current  = \Stripe\Subscription::retrieve(
            ['id' => $subscriptionId, 'expand' => ['items']],
            ['stripe_version' => self::PINNED_API_VERSION]
        );
        $item     = $current->items->data[0];
        $currency = $item->price->currency;

        // New immutable Price for the new amount/interval.
        $price = \Stripe\Price::create([
            'unit_amount'  => $amount,
            'currency'     => $currency,
            'recurring'    => ['interval' => $interval],
            'product_data' => ['name' => 'TECHO Donación Recurrente'],
        ], ['idempotency_key' => $idempotencyKey . ':price']);

        // Repoint the existing item to the new price. No proration: applies next cycle.
        return \Stripe\Subscription::update($subscriptionId, [
            'items'              => [['id' => $item->id, 'price' => $price->id]],
            'proration_behavior' => 'none',
            'expand'             => ['latest_invoice.payment_intent'],
        ], [
            'idempotency_key' => $idempotencyKey,
            'stripe_version'  => self::PINNED_API_VERSION,
        ]);
    }

    // ── Billing Portal ──────────────────────────────────────────────────────────

    /**
     * Create a Stripe Customer Portal session and return its URL.
     *
     * The portal lets the customer self-manage their recurring donations
     * (update payment method, change/cancel the subscription). Any change made
     * there is reflected back into our DB through the subscription webhooks.
     *
     * Requires the Customer Portal to be activated/configured in the Stripe
     * Dashboard for the donations account.
     *
     * @param  string $customerId Stripe Customer ID
     * @param  string $returnUrl  Where Stripe sends the user back when they exit
     * @return string             Absolute URL to open the portal
     * @throws ApiErrorException
     */
    public function createBillingPortalSession(string $customerId, string $returnUrl): string
    {
        $this->boot();

        $session = \Stripe\BillingPortal\Session::create([
            'customer'   => $customerId,
            'return_url' => $returnUrl,
        ]);

        return $session->url;
    }

    // ── Webhook ───────────────────────────────────────────────────────────────

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

    // ── Static helpers ────────────────────────────────────────────────────────

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

    /**
     * Map our `paymentMethod` value to Stripe's `payment_method_types` array.
     *
     * Apple Pay and Google Pay are wallet methods that route through the card
     * network on Stripe — ['card'] is correct server-side for both.
     * The client SDK (Stripe.js / mobile) handles the wallet UX automatically.
     * PIX is a separate method that requires its own type.
     */
    public static function resolvePaymentMethodTypes(string $paymentMethod): array
    {
        if ($paymentMethod === 'pix') {
            return ['pix'];
        }

        // 'card', 'apple_pay', 'google_pay' → all use the 'card' type server-side
        return ['card'];
    }
}
