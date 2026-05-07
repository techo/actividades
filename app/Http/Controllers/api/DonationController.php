<?php

namespace App\Http\Controllers\api;

use App\Donation;
use App\Http\Controllers\Controller;
use App\Services\StripePaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
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
     *   amount      (integer, minor units, required)
     *   currency    (string, ISO 4217 lowercase, required)
     *   source      (string, allowed values, required)
     *   countryCode (string, optional)
     *   idempotencyKey (string, optional — generated if omitted)
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
                'in:' . implode(',', StripePaymentService::ALLOWED_CURRENCIES),
            ],
            'source'         => [
                'required',
                'string',
                'in:' . implode(',', StripePaymentService::ALLOWED_SOURCES),
            ],
            'countryCode'    => ['nullable', 'string', 'max:10'],
            'idempotencyKey' => ['nullable', 'string', 'max:255'],
        ]);

        $personId       = Auth::user()->idPersona;
        $currency       = strtolower($data['currency']);
        $idempotencyKey = $data['idempotencyKey'] ?? (string) Str::uuid();

        // ── 2. Idempotency check — return existing record if key was seen ──
        $existing = Donation::where('person_id', $personId)
            ->where('idempotency_key', $idempotencyKey)
            ->first();

        if ($existing) {
            // Only replay a pending intent; terminal ones should not be reused
            if ($existing->status === Donation::STATUS_PENDING) {
                return response()->json([
                    'client_secret' => $this->extractClientSecret($existing),
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
                array_filter(['country_code' => $data['countryCode'] ?? null])
            );
        } catch (ApiErrorException $e) {
            return response()->json([
                'message' => 'Could not create payment intent.',
                'error'   => $e->getMessage(),
            ], 502);
        }

        // ── 4. Persist donation record ────────────────────────────────────
        $donation = Donation::create([
            'person_id'                 => $personId,
            'stripe_payment_intent_id'  => $intent->id,
            'amount'                    => $data['amount'],
            'currency'                  => $currency,
            'status'                    => Donation::STATUS_PENDING,
            'source'                    => $data['source'],
            'country_code'              => $data['countryCode'] ?? null,
            'idempotency_key'           => $idempotencyKey,
            'metadata'                  => [
                'stripe_status' => $intent->status,
            ],
        ]);

        // ── 5. Respond ────────────────────────────────────────────────────
        return response()->json([
            'client_secret' => $intent->client_secret,
            'intent_id'     => $intent->id,
        ], 200);
    }

    // =========================================================================
    // GET /api/donations/{intentId}/status
    // =========================================================================

    /**
     * Return the normalised status of a donation by its Stripe PI ID.
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

    // ── Private helpers ───────────────────────────────────────────────────

    /**
     * The client_secret is not stored in our DB (it's sensitive).
     * For a pending intent we have to re-fetch it from Stripe once on retry.
     */
    private function extractClientSecret(Donation $donation): ?string
    {
        try {
            \Stripe\Stripe::setApiKey(config('services.stripe_donations.secret'));
            $intent = \Stripe\PaymentIntent::retrieve($donation->stripe_payment_intent_id);
            return $intent->client_secret;
        } catch (\Exception $e) {
            return null;
        }
    }
}
