<?php

namespace App\Http\Controllers\api;

use App\Actividad;
use App\Donation;
use App\DonationPreset;
use App\DonationSubscription;
use App\Http\Controllers\Controller;
use App\Services\StripePaymentService;
use App\StripeCustomer;
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
    // GET /api/donations/stripe/checkout-config
    // =========================================================================

    /**
     * Devuelve la moneda local y los tres montos sugeridos para el país del
     * usuario autenticado (Persona.idPais). Los montos están fijados en BD por
     * país (tabla donation_presets), no se calculan por tipo de cambio.
     *
     * Si el país no tiene presets configurados, cae al default global USD 5/10/20.
     *
     * Los montos van en UNIDAD MAYOR (presets_major). El cliente debe convertir
     * a unidad menor usando minor_unit_exponent antes de llamar a Stripe
     * (ej. mxn: 34 → 34 * 10^2 = 3400).
     *
     * Response 200:
     *   { id_pais, country_code, currency, presets_major{bajo,medio,alto},
     *     minor_unit_exponent, pix_enabled }
     */
    public function checkoutConfig(): JsonResponse
    {
        $user   = Auth::user();
        $idPais = $user->idPais;
        $iso2   = optional($user->pais)->iso2;

        $preset = $idPais
            ? DonationPreset::where('id_pais', $idPais)->first()
            : null;

        if ($preset) {
            $currency = $preset->currency;
            $presets  = [
                'bajo'  => $preset->preset_low,
                'medio' => $preset->preset_mid,
                'alto'  => $preset->preset_high,
            ];
            $exponent = $preset->minor_unit_exponent;
            $pix      = $preset->pix_enabled;
        } else {
            // País sin presets configurados → default global.
            $currency = DonationPreset::DEFAULT_CURRENCY;
            $presets  = DonationPreset::DEFAULT_PRESETS;
            $exponent = DonationPreset::DEFAULT_EXPONENT;
            $pix      = false;
        }

        return response()->json([
            'id_pais'             => $idPais !== null ? (int) $idPais : null,
            'country_code'        => $iso2 ? strtoupper($iso2) : null,
            'currency'            => $currency,
            'presets_major'       => $presets,
            'minor_unit_exponent' => $exponent,
            'pix_enabled'         => $pix,
        ]);
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

        $user           = Auth::user();
        $personId       = $user->idPersona;
        $currency       = strtolower($data['currency']);
        $mode           = $data['mode'] ?? 'one_time';
        $paymentMethod  = $data['paymentMethod'] ?? 'card';
        $idempotencyKey = $data['idempotencyKey'] ?? (string) Str::uuid();
        $payerName      = trim(($user->nombres ?? '') . ' ' . ($user->apellidoPaterno ?? ''));
        $payerEmail     = $user->mail ?? '';
        $payerTaxId     = $user->dni  ?? '';

        // ── 2. Idempotency check — return existing record if key was seen ──
        $existing = Donation::where('person_id', $personId)
            ->where('idempotency_key', $idempotencyKey)
            ->first();

        if ($existing) {
            if ($existing->status === Donation::STATUS_PENDING) {
                $intent  = $this->stripe->retrievePaymentIntent($existing->stripe_payment_intent_id);
                $reponse = [
                    'intent_id'     => $existing->stripe_payment_intent_id,
                    'client_secret' => $intent->client_secret ?? null,
                ];
                if ($paymentMethod === 'pix') {
                    $pix = $intent->next_action->pix_display_qr_code ?? null;
                    $reponse['pix'] = [
                        'copy_paste_code' => $pix->data          ?? null,
                        'qr_code_url'     => $pix->image_url_png ?? null,
                        'expires_at'      => isset($pix->expires_at)
                            ? Carbon::createFromTimestamp($pix->expires_at)->toIso8601String()
                            : null,
                    ];
                }
                return response()->json($reponse);
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
                ]),
                $payerName,
                $payerEmail,
                $payerTaxId
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
        $response = [
            'intent_id'     => $intent->id,
            'client_secret' => $intent->client_secret,
        ];

        // PIX: confirmed server-side, so Stripe already generated the QR code.
        // Return it directly so the app can render it without an extra round-trip.
        if ($paymentMethod === 'pix') {
            $pix = $intent->next_action->pix_display_qr_code ?? null;
            $response['pix'] = [
                'copy_paste_code' => $pix->data          ?? null,
                'qr_code_url'     => $pix->image_url_png ?? null,
                'expires_at'      => isset($pix->expires_at)
                    ? Carbon::createFromTimestamp($pix->expires_at)->toIso8601String()
                    : null,
            ];
        }

        return response()->json($response);
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
    // GET /api/donations/stripe/subscription
    // =========================================================================

    /**
     * List all non-terminal subscriptions for the authenticated user.
     *
     * Returns subscriptions with status: incomplete | active | past_due | unpaid
     * Excludes: incomplete_expired | canceled
     *
     * Response 200:
     *   { data: [ { subscription_id, status, amount, currency, interval,
     *               current_period_end, created_at } ] }
     */
    public function listSubscriptions(): JsonResponse
    {
        $subscriptions = DonationSubscription::where('person_id', Auth::user()->idPersona)
            ->whereNotIn('status', DonationSubscription::TERMINAL_STATUSES)
            ->orderByDesc('created_at')
            ->get()
            ->map(function (DonationSubscription $sub) {
                return [
                    'subscription_id'    => $sub->stripe_subscription_id,
                    'status'             => $sub->status,
                    'amount'             => $sub->amount,
                    'currency'           => $sub->currency,
                    'interval'           => $sub->interval,
                    'current_period_end'   => $sub->current_period_end
                        ? $sub->current_period_end->toIso8601String()
                        : null,
                    'cancel_at_period_end' => (bool) $sub->cancel_at_period_end,
                    'canceled_at'          => $sub->canceled_at
                        ? $sub->canceled_at->toIso8601String()
                        : null,
                    'created_at'           => $sub->created_at->toIso8601String(),
                ];
            });

        return response()->json(['data' => $subscriptions]);
    }

    // =========================================================================
    // POST /api/donations/stripe/billing-portal
    // =========================================================================

    /**
     * Create a Stripe Customer Portal session and return its URL so the app
     * can open it in the browser. From the portal the user can update or cancel
     * their recurring donations; the resulting changes flow back into our DB
     * via the subscription webhooks.
     *
     * Request body (optional):
     *   return_url | returnUrl  (string) — where Stripe sends the user on exit.
     *                                       Defaults to the MiTECHO deep link.
     *
     * Response 200:
     *   { url }
     *
     * Errors:
     *   404 — the user has no Stripe customer yet (never started a subscription)
     *   502 — Stripe could not create the portal session
     */
    public function billingPortal(Request $request): JsonResponse
    {
        // Accept both snake_case and camelCase. Validate as a plain string —
        // NOT the `url` rule — because the mobile return URL is a custom scheme
        // deep link (e.g. mitecho://…) which the `url` rule would reject.
        $request->validate([
            'return_url' => ['nullable', 'string', 'max:500'],
            'returnUrl'  => ['nullable', 'string', 'max:500'],
        ]);

        $returnUrl = $request->input('return_url')
            ?? $request->input('returnUrl')
            ?? 'mitecho://stripe-billing-portal-return';

        $customer = StripeCustomer::where('person_id', Auth::user()->idPersona)->first();

        if (!$customer) {
            return response()->json([
                'message' => 'No Stripe customer found for this user.',
            ], 404);
        }

        try {
            $url = $this->stripe->createBillingPortalSession(
                $customer->stripe_customer_id,
                $returnUrl
            );
        } catch (ApiErrorException $e) {
            return response()->json([
                'message' => 'Could not create billing portal session.',
                'error'   => $e->getMessage(),
            ], 502);
        }

        return response()->json(['url' => $url]);
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
            'interval'             => $subscription->interval,
            'current_period_end'   => $subscription->current_period_end
                ? $subscription->current_period_end->toIso8601String()
                : null,
            'cancel_at_period_end' => (bool) $subscription->cancel_at_period_end,
            'canceled_at'          => $subscription->canceled_at
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

    // =========================================================================
    // GET /api/donations/history
    // =========================================================================

    /**
     * Unified donation history for the authenticated user.
     * Merges one-time donations and subscriptions into a single chronological list.
     *
     * Query params (all optional):
     *   type    = one_time | subscription
     *   status  = succeeded | pending | failed | canceled | active | past_due …
     *   from    = ISO date (e.g. 2026-01-01)
     *   limit   = integer (default 20, max 100)
     *   page    = integer (default 1)
     *
     * Response 200:
     *   { data: [...], meta: { total, page, limit } }
     */
    public function history(Request $request): JsonResponse
    {
        $personId = Auth::user()->idPersona;
        $type     = $request->query('type');
        $status   = $request->query('status');
        $from     = $request->query('from');
        $limit    = min((int) ($request->query('limit', 20)), 100);
        $page     = max((int) ($request->query('page', 1)), 1);

        $oneTimeItems    = collect();
        $subscriptionItems = collect();

        // ── One-time donations ────────────────────────────────────────────────
        if (!$type || $type === 'one_time') {
            $q = Donation::where('person_id', $personId);
            if ($status) $q->where('status', $status);
            if ($from)   $q->where('created_at', '>=', Carbon::parse($from)->startOfDay());

            $donations = $q->orderByDesc('created_at')->get();

            // Bulk-load linked actividades to avoid N+1
            $inscripcionIds = $donations->pluck('inscripcion_id')->filter()->unique()->values();
            $actividadesMap = collect();
            if ($inscripcionIds->isNotEmpty()) {
                $actividadesMap = \App\Inscripcion::whereIn('idInscripcion', $inscripcionIds)
                    ->with('actividad:idActividad,nombreActividad')
                    ->get()
                    ->keyBy('idInscripcion');
            }

            $oneTimeItems = $donations->map(function (Donation $d) use ($actividadesMap) {
                $actividad = null;
                if ($d->inscripcion_id && $actividadesMap->has($d->inscripcion_id)) {
                    $act = $actividadesMap[$d->inscripcion_id]->actividad;
                    $actividad = $act ? [
                        'id'     => $act->idActividad,
                        'nombre' => $act->nombreActividad,
                    ] : null;
                }
                return [
                    'type'           => 'one_time',
                    'id'             => $d->stripe_payment_intent_id,
                    'amount'         => $d->amount,
                    'currency'       => $d->currency,
                    'status'         => $d->status,
                    'source'         => $d->source,
                    'payment_method' => $d->payment_method_type,
                    'inscripcion_id' => $d->inscripcion_id,
                    'actividad'      => $actividad,
                    'paid_at'        => $d->paid_at ? $d->paid_at->toIso8601String() : null,
                    'created_at'     => $d->created_at->toIso8601String(),
                ];
            });
        }

        // ── Subscriptions ─────────────────────────────────────────────────────
        if (!$type || $type === 'subscription') {
            $q = DonationSubscription::where('person_id', $personId);
            if ($status) $q->where('status', $status);
            if ($from)   $q->where('created_at', '>=', Carbon::parse($from)->startOfDay());

            $subscriptionItems = $q->orderByDesc('created_at')->get()
                ->map(function (DonationSubscription $s) {
                    return [
                        'type'               => 'subscription',
                        'id'                 => $s->stripe_subscription_id,
                        'amount'             => $s->amount,
                        'currency'           => $s->currency,
                        'status'             => $s->status,
                        'source'             => $s->source,
                        'interval'           => $s->interval,
                        'inscripcion_id'     => null,
                        'actividad'          => null,
                        'current_period_end'   => $s->current_period_end
                            ? $s->current_period_end->toIso8601String()
                            : null,
                        'cancel_at_period_end' => (bool) $s->cancel_at_period_end,
                        'canceled_at'          => $s->canceled_at
                            ? $s->canceled_at->toIso8601String()
                            : null,
                        'created_at'           => $s->created_at->toIso8601String(),
                    ];
                });
        }

        // ── Merge, sort by date desc, paginate ────────────────────────────────
        $all = $oneTimeItems->concat($subscriptionItems)
            ->sortByDesc('created_at')
            ->values();

        $total  = $all->count();
        $offset = ($page - 1) * $limit;
        $paged  = $all->slice($offset, $limit)->values();

        return response()->json([
            'data' => $paged,
            'meta' => [
                'total' => $total,
                'page'  => $page,
                'limit' => $limit,
            ],
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
