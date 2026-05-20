<?php

namespace App\Http\Controllers\api;

use App\Donation;
use App\DonationSubscription;
use App\Http\Controllers\Controller;
use App\Services\StripePaymentService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Stripe\Exception\ApiErrorException;

class DonationController extends Controller
{
    /** @var StripePaymentService */
    protected $stripe;

    public function __construct(StripePaymentService $stripe)
    {
        $this->stripe = $stripe;
    }

    // =========================================================================
    // POST /api/donations/stripe/payment-intent
    // =========================================================================

    /**
     * Create a Stripe PaymentIntent and persist a donation record.
     *
     * Request body:
     *   amount          (integer, minor units, required)
     *   currency        (string, ISO 4217 lowercase, required)
     *   source          (string, allowed values, required)
     *   mode            (string, 'one_time'|'recurring', optional — default one_time)
     *   paymentMethod   (string, 'card'|'apple_pay'|'google_pay'|'pix', optional — default card)
     *   interval        (string, 'month'|'year', required when mode=recurring)
     *   countryCode     (string, optional)
     *   idempotencyKey  (string, optional — generated if omitted)
     *
     * Response 200:
     *   { client_secret, intent_id }
     */
    public function createPaymentIntent(Request $request): JsonResponse
    {
        // ── 1. Validate input ─────────────────────────────────────────────
        $data = $request->validate([
            'amount'         => [
                'required',
                'integer',
                'min:' . StripePaymentService::AMOUNT_MIN,
                'max:' . StripePaymentService::AMOUNT_MAX,
            ],
            'currency'       => [
                'required',
                'string',
                Rule::in(StripePaymentService::ALLOWED_CURRENCIES),
            ],
            'source'         => [
                'required',
                'string',
                Rule::in(StripePaymentService::ALLOWED_SOURCES),
            ],
            'mode'           => [
                'nullable',
                'string',
                Rule::in(StripePaymentService::ALLOWED_MODES),
            ],
            'paymentMethod'  => [
                'nullable',
                'string',
                Rule::in(StripePaymentService::ALLOWED_PAYMENT_METHODS),
            ],
            'interval'       => [
                'nullable',
                'string',
                Rule::in(StripePaymentService::ALLOWED_INTERVALS),
                'required_if:mode,recurring',
            ],
            'countryCode'    => ['nullable', 'string', 'max:10'],
            'idempotencyKey' => ['nullable', 'string', 'max:255'],
        ]);

        $personId       = Auth::user()->idPersona;
        $currency       = strtolower($data['currency']);
        $mode           = $data['mode'] ?? 'one_time';
        $paymentMethod  = $data['paymentMethod'] ?? 'card';
        $idempotencyKey = $data['idempotencyKey'] ?? (string) Str::uuid();

        // ── 2. Idempotency check — return existing record if key was seen ──
        $existing = Donation::where('person_id', $personId)
            ->where('idempotency_key', $idempotencyKey)
            ->first();

        if ($existing) {
            if ($existing->status === Donation::STATUS_PENDING) {
                return response()->json([
                    'client_secret' => $this->extractPaymentIntentSecret($existing),
                    'intent_id'     => $existing->stripe_payment_intent_id,
                ]);
            }

            return response()->json([
                'message' => 'This idempotency key belongs to a completed or canceled donation.',
            ], 422);
        }

        // ── 3. Create Stripe PaymentIntent ────────────────────────────────
        try {
            $intent = $this->stripe->createPaymentIntent(
                (int) $data['amount'],
                $currency,
                $personId,
                $data['source'],
                $idempotencyKey,
                $paymentMethod,
                array_filter([
                    'country_code' => $data['countryCode'] ?? null,
                    'mode'         => $mode,
                ])
            );
        } catch (ApiErrorException $e) {
            return response()->json([
                'message' => 'Could not create payment intent.',
                'error'   => $e->getMessage(),
            ], 502);
        }

        // ── 4. Persist donation record ────────────────────────────────────
        Donation::create([
            'person_id'                => $personId,
            'stripe_payment_intent_id' => $intent->id,
            'amount'                   => $data['amount'],
            'currency'                 => $currency,
            'mode'                     => $mode,
            'payment_method_type'      => $paymentMethod,
            'status'                   => Donation::STATUS_PENDING,
            'source'                   => $data['source'],
            'country_code'             => $data['countryCode'] ?? null,
            'idempotency_key'          => $idempotencyKey,
            'metadata'                 => ['stripe_status' => $intent->status],
        ]);

        // ── 5. Respond ────────────────────────────────────────────────────
        return response()->json([
            'client_secret' => $intent->client_secret,
            'intent_id'     => $intent->id,
        ]);
    }

    // =========================================================================
    // POST /api/donations/stripe/subscription
    // =========================================================================

    /**
     * Create a Stripe Subscription (recurring donation) and persist the record.
     *
     * Request body:
     *   amount          (integer, minor units, required)
     *   currency        (string, ISO 4217 lowercase, required)
     *   source          (string, required)
     *   mode            (string, must be 'recurring', required)
     *   interval        (string, 'month'|'year', required)
     *   countryCode     (string, optional)
     *   idempotencyKey  (string, optional — generated if omitted)
     *
     * Response 200:
     *   { client_secret, subscription_id, status }
     *
     * The app must confirm the first payment using client_secret via the
     * Stripe SDK (same flow as a PaymentIntent). Subsequent charges are
     * automatic and tracked via webhooks.
     */
    public function createSubscription(Request $request): JsonResponse
    {
        // ── 1. Validate input ─────────────────────────────────────────────
        $data = $request->validate([
            'amount'         => [
                'required',
                'integer',
                'min:' . StripePaymentService::AMOUNT_MIN,
                'max:' . StripePaymentService::AMOUNT_MAX,
            ],
            'currency'       => [
                'required',
                'string',
                Rule::in(StripePaymentService::ALLOWED_CURRENCIES),
            ],
            'source'         => [
                'required',
                'string',
                Rule::in(StripePaymentService::ALLOWED_SOURCES),
            ],
            'mode'           => [
                'required',
                'string',
                Rule::in(['recurring']),
            ],
            'interval'       => [
                'required',
                'string',
                Rule::in(StripePaymentService::ALLOWED_INTERVALS),
            ],
            'countryCode'    => ['nullable', 'string', 'max:10'],
            'idempotencyKey' => ['nullable', 'string', 'max:255'],
        ]);

        $personId       = Auth::user()->idPersona;
        $currency       = strtolower($data['currency']);
        $idempotencyKey = $data['idempotencyKey'] ?? (string) Str::uuid();

        // ── 2. Idempotency check ──────────────────────────────────────────
        $existing = DonationSubscription::where('person_id', $personId)
            ->where('idempotency_key', $idempotencyKey)
            ->first();

        if ($existing) {
            if ($existing->isTerminal()) {
                return response()->json([
                    'message' => 'This idempotency key belongs to a canceled or expired subscription.',
                ], 422);
            }

            // Replay — re-fetch the client_secret from Stripe
            return response()->json([
                'client_secret'   => $this->extractSubscriptionSecret($existing),
                'subscription_id' => $existing->stripe_subscription_id,
                'status'          => $existing->status,
            ]);
        }

        // ── 3. Get or create Stripe Customer ──────────────────────────────
        try {
            $customerId = $this->stripe->getOrCreateCustomer(
                $personId,
                Auth::user()->mail
            );
        } catch (ApiErrorException $e) {
            return response()->json([
                'message' => 'Could not create Stripe customer.',
                'error'   => $e->getMessage(),
            ], 502);
        }

        // ── 4. Create Stripe Subscription ─────────────────────────────────
        try {
            $subscription = $this->stripe->createSubscription(
                $customerId,
                (int) $data['amount'],
                $currency,
                $data['interval'],
                $personId,
                $data['source'],
                $idempotencyKey,
                array_filter(['country_code' => $data['countryCode'] ?? null])
            );
        } catch (ApiErrorException $e) {
            return response()->json([
                'message' => 'Could not create subscription.',
                'error'   => $e->getMessage(),
            ], 502);
        }

        // ── 5. Extract client_secret for the first payment ────────────────
        // Stripe expands latest_invoice.payment_intent when default_incomplete
        $clientSecret = $subscription->latest_invoice->payment_intent->client_secret
            ?? null;

        // ── 6. Persist subscription record ────────────────────────────────
        DonationSubscription::create([
            'person_id'              => $personId,
            'stripe_subscription_id' => $subscription->id,
            'stripe_customer_id'     => $customerId,
            'amount'                 => $data['amount'],
            'currency'               => $currency,
            'interval'               => $data['interval'],
            'status'                 => $subscription->status,
            'source'                 => $data['source'],
            'country_code'           => $data['countryCode'] ?? null,
            'idempotency_key'        => $idempotencyKey,
            'current_period_end'     => $subscription->current_period_end
                ? Carbon::createFromTimestamp($subscription->current_period_end)
                : null,
            'metadata'               => ['stripe_status' => $subscription->status],
        ]);

        // ── 7. Respond ────────────────────────────────────────────────────
        return response()->json([
            'client_secret'   => $clientSecret,
            'subscription_id' => $subscription->id,
            'status'          => $subscription->status,
        ]);
    }

    // =========================================================================
    // GET /api/donations/stripe/subscription/{subscriptionId}/status
    // =========================================================================

    /**
     * Return the status of a recurring donation subscription.
     * Reads only from our DB — no live Stripe API call on every poll.
     *
     * Response 200:
     *   { subscription_id, status, amount, currency, interval,
     *     current_period_end, canceled_at }
     */
    public function getSubscriptionStatus(string $subscriptionId): JsonResponse
    {
        $subscription = DonationSubscription::where('stripe_subscription_id', $subscriptionId)
            ->where('person_id', Auth::user()->idPersona)
            ->first();

        if (!$subscription) {
            return response()->json(['message' => 'Subscription not found.'], 404);
        }

        return response()->json([
            'subscription_id'    => $subscription->stripe_subscription_id,
            'status'             => $subscription->status,
            'amount'             => $subscription->amount,
            'currency'           => $subscription->currency,
            'interval'           => $subscription->interval,
            'current_period_end' => $subscription->current_period_end
                ? $subscription->current_period_end->toIso8601String()
                : null,
            'canceled_at'        => $subscription->canceled_at
                ? $subscription->canceled_at->toIso8601String()
                : null,
        ]);
    }

    // =========================================================================
    // GET /api/donations/{intentId}/status
    // =========================================================================

    /**
     * Return the normalised status of a one-time donation by its Stripe PI ID.
     * Reads only from our DB — does NOT call Stripe on every request.
     *
     * Response 200:
     *   { intent_id, status, amount, currency, paid_at }
     */
    public function getStatus(string $intentId): JsonResponse
    {
        $donation = Donation::where('stripe_payment_intent_id', $intentId)
            ->where('person_id', Auth::user()->idPersona)
            ->first();

        if (!$donation) {
            return response()->json(['message' => 'Donation not found.'], 404);
        }

        return response()->json([
            'intent_id' => $donation->stripe_payment_intent_id,
            'status'    => $donation->status,
            'amount'    => $donation->amount,
            'currency'  => $donation->currency,
            'paid_at'   => $donation->paid_at ? $donation->paid_at->toIso8601String() : null,
        ]);
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    /**
     * The client_secret is not stored in our DB (sensitive).
     * For a pending PaymentIntent we re-fetch it from Stripe once on retry.
     */
    private function extractPaymentIntentSecret(Donation $donation): ?string
    {
        try {
            \Stripe\Stripe::setApiKey(config('services.stripe_donations.secret'));
            $intent = \Stripe\PaymentIntent::retrieve($donation->stripe_payment_intent_id);
            return $intent->client_secret;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Re-fetch the client_secret for the first unpaid invoice of a subscription.
     * Used on idempotent replay when the subscription already exists.
     */
    private function extractSubscriptionSecret(DonationSubscription $sub): ?string
    {
        try {
            $stripe = $this->stripe->retrieveSubscription($sub->stripe_subscription_id);
            return $stripe->latest_invoice->payment_intent->client_secret ?? null;
        } catch (\Exception $e) {
            return null;
        }
    }
}
