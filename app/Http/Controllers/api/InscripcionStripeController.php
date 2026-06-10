<?php

namespace App\Http\Controllers\api;

use App\Donation;
use App\Http\Controllers\Controller;
use App\Inscripcion;
use App\Mail\MailInscripcionConfirmada;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * Stripe PaymentIntent flow for activity enrollment payments (mobile app).
 *
 * This is separate from StripeController (which handles Checkout Sessions for web)
 * and DonationController (which handles free donations).
 *
 * Flow:
 *   1. POST /api/inscripciones/{id}/stripe/payment-intent
 *      → creates a PI using the country's Stripe key
 *      → returns client_secret for the mobile SDK to confirm
 *
 *   2. Mobile SDK confirms the payment (stripe.confirmPayment)
 *
 *   3. Stripe fires payment_intent.succeeded webhook to /stripe/webhook/{paisId}
 *      → existing StripeController::webhook() now also handles PI events
 *      → marks Inscripcion.pago = 1 and creates a Donation record linked to inscripcion_id
 */
class InscripcionStripeController extends Controller
{
    // =========================================================================
    // POST /api/inscripciones/{id}/stripe/payment-intent
    // =========================================================================

    /**
     * Create a PaymentIntent for paying an activity enrollment via the mobile app.
     *
     * Uses the Stripe key configured for the activity's country (pais.config_pago),
     * same key used by the web Checkout Session flow.
     *
     * Request body (all optional — defaults come from the activity):
     *   idempotencyKey  string   client-supplied idempotency key
     *
     * Response 200:
     *   { client_secret, intent_id, amount, currency }
     */
    public function createPaymentIntent(Request $request, int $idInscripcion): JsonResponse
    {
        // ── 1. Load and authorise ─────────────────────────────────────────────
        $inscripcion = Inscripcion::where('idInscripcion', $idInscripcion)
            ->where('idPersona', Auth::user()->idPersona)
            ->with(['actividad.pais', 'persona'])
            ->firstOrFail();

        if ($inscripcion->pago == 1) {
            return response()->json(['message' => 'Esta inscripción ya fue pagada.'], 422);
        }

        $actividad = $inscripcion->actividad;

        // ── 2. Resolve country Stripe key ─────────────────────────────────────
        $config = json_decode($actividad->pais->config_pago ?? '{}');

        if (empty($config->stripe_secret)) {
            return response()->json([
                'message' => 'Este país no tiene Stripe configurado.',
            ], 422);
        }

        // ── 3. Resolve amount and currency ────────────────────────────────────
        $montoCentavos = (int) round((float) ($actividad->montoMin ?? $actividad->costo ?? 0) * 100);

        if ($montoCentavos <= 0) {
            return response()->json([
                'message' => 'El monto de pago no está configurado correctamente.',
            ], 422);
        }

        $currency      = strtolower($actividad->moneda ?: $this->monedaPorPais($actividad->pais->iso2 ?? null));
        $idempotencyKey = $request->input('idempotencyKey')
            ?? 'inscripcion-' . $idInscripcion . '-' . Auth::user()->idPersona;

        // ── 4. Idempotency — return existing pending PI if key was seen ────────
        $existing = Donation::where('inscripcion_id', $idInscripcion)
            ->where('person_id', Auth::user()->idPersona)
            ->where('status', Donation::STATUS_PENDING)
            ->first();

        if ($existing) {
            \Stripe\Stripe::setApiKey($config->stripe_secret);
            try {
                $intent = \Stripe\PaymentIntent::retrieve($existing->stripe_payment_intent_id);
                return response()->json([
                    'client_secret' => $intent->client_secret,
                    'intent_id'     => $intent->id,
                    'amount'        => $montoCentavos,
                    'currency'      => $currency,
                ]);
            } catch (\Exception $e) {
                // PI not found or expired — fall through to create a new one
            }
        }

        // ── 5. Create Stripe PaymentIntent with country key ───────────────────
        \Stripe\Stripe::setApiKey($config->stripe_secret);

        try {
            $intent = \Stripe\PaymentIntent::create([
                'amount'                    => $montoCentavos,
                'currency'                  => $currency,
                'automatic_payment_methods' => ['enabled' => true],
                'metadata'                  => [
                    'inscripcion_id' => $idInscripcion,
                    'person_id'      => Auth::user()->idPersona,
                    'actividad_id'   => $actividad->idActividad,
                    'pais_id'        => $actividad->pais->id,
                ],
            ], ['idempotency_key' => $idempotencyKey]);
        } catch (\Stripe\Exception\ApiErrorException $e) {
            Log::error('InscripcionStripe: error creando PI para inscripcion ' . $idInscripcion . ': ' . $e->getMessage());
            return response()->json([
                'message' => 'No se pudo iniciar el pago.',
                'error'   => $e->getMessage(),
            ], 502);
        }

        // ── 6. Persist donation record linked to the inscripcion ──────────────
        Donation::create([
            'person_id'                => Auth::user()->idPersona,
            'inscripcion_id'           => $idInscripcion,
            'stripe_payment_intent_id' => $intent->id,
            'amount'                   => $montoCentavos,
            'currency'                 => $currency,
            'mode'                     => 'one_time',
            'payment_method_type'      => 'card',
            'status'                   => Donation::STATUS_PENDING,
            'source'                   => 'inscripcion',
            'country_code'             => $actividad->pais->id ?? null,
            'idempotency_key'          => $idempotencyKey,
            'metadata'                 => [
                'actividad_id' => $actividad->idActividad,
                'stripe_key'   => 'pais_' . ($actividad->pais->id ?? 'unknown'),
            ],
        ]);

        // ── 7. Store PI id on the inscripcion for the webhook to find it ──────
        $inscripcion->stripe_payment_intent_id = $intent->id;
        $inscripcion->save();

        return response()->json([
            'client_secret' => $intent->client_secret,
            'intent_id'     => $intent->id,
            'amount'        => $montoCentavos,
            'currency'      => $currency,
        ]);
    }

    /**
     * Moneda ISO por defecto según el código ISO2 del país.
     * Fuente de verdad para cuando la actividad no tiene moneda seteada.
     */
    private function monedaPorPais(?string $iso2): string
    {
        $mapa = [
            'ar' => 'ars', // Argentina
            'bo' => 'bob', // Bolivia
            'br' => 'brl', // Brasil
            'co' => 'cop', // Colombia
            'cr' => 'crc', // Costa Rica
            'do' => 'dop', // República Dominicana
            'ec' => 'usd', // Ecuador (dolarizado)
            'sv' => 'usd', // El Salvador (dolarizado)
            'gt' => 'gtq', // Guatemala
            'hn' => 'hnl', // Honduras
            'mx' => 'mxn', // México
            'pa' => 'usd', // Panamá (dolarizado)
            'py' => 'pyg', // Paraguay
            'pe' => 'pen', // Perú
            'uy' => 'uyu', // Uruguay
            've' => 'usd', // Venezuela
        ];

        return $mapa[strtolower($iso2 ?? '')] ?? 'usd';
    }
}
