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
use Illuminate\Validation\Rule;

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
 *      — PIX (paymentMethod=pix, solo BRL): el PI se confirma server-side y la
 *        respuesta ya trae el bloque `pix` (copia-e-cola + QR). La app solo lo
 *        muestra; no llama al SDK. Mismo contrato que /api/donations.
 *
 *   3. Stripe fires payment_intent.succeeded webhook to /stripe/webhook/{paisId}
 *      → existing StripeController::webhook() now also handles PI events
 *      → marks Inscripcion.pago = 1 and creates a Donation record linked to inscripcion_id
 */
class InscripcionStripeController extends Controller
{
    /** Métodos que acepta `paymentMethod`. 'card' incluye wallets (automatic_payment_methods). */
    const METODOS_PAGO = ['card', 'pix'];

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
     *   paymentMethod   string   'card' (default) | 'pix' (solo moneda brl)
     *
     * Response 200:
     *   { client_secret, intent_id, amount, currency, publishable_key }
     *   + pix: { copy_paste_code, qr_code_url, expires_at } cuando paymentMethod=pix
     */
    public function createPaymentIntent(Request $request, int $idInscripcion): JsonResponse
    {
        $data = $request->validate([
            'paymentMethod'  => ['nullable', 'string', Rule::in(self::METODOS_PAGO)],
            'idempotencyKey' => ['nullable', 'string', 'max:255'],
        ]);
        $paymentMethod = $data['paymentMethod'] ?? 'card';

        // ── 1. Load and authorise ─────────────────────────────────────────────
        $inscripcion = Inscripcion::where('idInscripcion', $idInscripcion)
            ->where('idPersona', Auth::user()->idPersona)
            ->with(['actividad.pais', 'persona'])
            ->firstOrFail();

        if ($inscripcion->pago == 1) {
            return response()->json(['message' => 'Esta inscripción ya fue pagada.'], 422);
        }

        $actividad = $inscripcion->actividad;

        // ── 1b. Defensa server-side: un socio nunca debe ser cobrado ──────────
        // Si la inscripción ya está exenta, o la persona es socio (re-evaluado en
        // vivo por si la materialización previa se salteó), no se crea PaymentIntent.
        $exencion = app(\App\Services\Salesforce\SocioExencionService::class);
        if ($exencion->aplicarSiCorresponde($inscripcion, $actividad, $inscripcion->persona)) {
            if ($inscripcion->isDirty()) {
                $inscripcion->save();
            }
            return response()->json([
                'exento'  => true,
                'motivo'  => $inscripcion->exento_motivo,
                'message' => 'Por ser socio no tenés que pagar esta actividad.',
            ], 200);
        }

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

        if ($paymentMethod === 'pix' && $currency !== 'brl') {
            return response()->json([
                'message' => 'PIX solo está disponible para pagos en reales (BRL).',
            ], 422);
        }

        // PIX: cada QR vence (24 hs por defecto) y uno vencido no se puede
        // reusar, así que la key por defecto no es fija; el reuso de un PIX
        // vigente lo resuelve el paso 4.
        $idempotencyKey = $data['idempotencyKey']
            ?? ($paymentMethod === 'pix'
                ? 'inscripcion-' . $idInscripcion . '-' . Auth::user()->idPersona . '-pix-' . Str::random(12)
                : 'inscripcion-' . $idInscripcion . '-' . Auth::user()->idPersona);

        // ── 4. Idempotency — return existing pending PI of the same method ────
        $existing = Donation::where('inscripcion_id', $idInscripcion)
            ->where('person_id', Auth::user()->idPersona)
            ->where('status', Donation::STATUS_PENDING)
            ->where(function ($q) use ($paymentMethod) {
                // Filas previas a PIX pueden no tener método: cuentan como card.
                $q->where('payment_method_type', $paymentMethod);
                if ($paymentMethod === 'card') {
                    $q->orWhereNull('payment_method_type');
                }
            })
            ->latest('id')
            ->first();

        if ($existing) {
            \Stripe\Stripe::setApiKey($config->stripe_secret);
            try {
                $intent = \Stripe\PaymentIntent::retrieve($existing->stripe_payment_intent_id);
                $pix    = $paymentMethod === 'pix' ? $this->pixData($intent) : null;

                // Un PIX sin QR vigente (vencido o ya no esperando pago) no
                // sirve: se crea uno nuevo.
                if ($paymentMethod !== 'pix' || $this->pixVigente($pix)) {
                    $response = [
                        'client_secret'   => $intent->client_secret,
                        'intent_id'       => $intent->id,
                        'amount'          => $montoCentavos,
                        'currency'        => $currency,
                        'publishable_key' => $config->stripe_public ?? null,
                    ];
                    if ($pix) {
                        $response['pix'] = $pix;
                    }
                    return response()->json($response);
                }
            } catch (\Exception $e) {
                // PI not found or expired — fall through to create a new one
            }
        }

        // ── 5. Create Stripe PaymentIntent with country key ───────────────────
        \Stripe\Stripe::setApiKey($config->stripe_secret);

        $metadata = [
            'inscripcion_id' => $idInscripcion,
            'person_id'      => Auth::user()->idPersona,
            'actividad_id'   => $actividad->idActividad,
            'pais_id'        => $actividad->pais->id,
        ];

        if ($paymentMethod === 'pix') {
            // PIX: se confirma server-side para que Stripe genere el QR /
            // copia-e-cola en next_action.pix_display_qr_code (igual que en
            // StripePaymentService::createPaymentIntent para donaciones).
            // La confirmación server-side exige billing_details.name y no admite
            // automatic_payment_methods. El pago llega por el mismo webhook
            // payment_intent.succeeded (busca por metadata.inscripcion_id).
            $persona = $inscripcion->persona;
            $params  = [
                'amount'               => $montoCentavos,
                'currency'             => $currency,
                'payment_method_types' => ['pix'],
                'payment_method_data'  => [
                    'type'            => 'pix',
                    'billing_details' => array_filter([
                        'name'   => trim(($persona->nombres ?? '') . ' ' . ($persona->apellidoPaterno ?? '')) ?: 'Voluntário TETO',
                        'email'  => $persona->mail ?: null,
                        'tax_id' => $persona->dni ?: null,
                    ]),
                ],
                'confirm'              => true,
                'metadata'             => $metadata,
            ];
        } else {
            $params = [
                'amount'                    => $montoCentavos,
                'currency'                  => $currency,
                'automatic_payment_methods' => ['enabled' => true],
                'metadata'                  => $metadata,
            ];
        }

        try {
            $intent = \Stripe\PaymentIntent::create($params, ['idempotency_key' => $idempotencyKey]);
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
            'payment_method_type'      => $paymentMethod,
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

        $response = [
            'client_secret'   => $intent->client_secret,
            'intent_id'       => $intent->id,
            'amount'          => $montoCentavos,
            'currency'        => $currency,
            'publishable_key' => $config->stripe_public ?? null,
        ];

        if ($paymentMethod === 'pix') {
            $response['pix'] = $this->pixData($intent);
        }

        return response()->json($response);
    }

    /**
     * Bloque `pix` de la respuesta (mismo formato que /api/donations):
     * { copy_paste_code, qr_code_url, expires_at (ISO 8601) }.
     */
    private function pixData($intent): array
    {
        $pix = $intent->next_action->pix_display_qr_code ?? null;

        return [
            'copy_paste_code' => $pix->data          ?? null,
            'qr_code_url'     => $pix->image_url_png ?? null,
            'expires_at'      => isset($pix->expires_at)
                ? Carbon::createFromTimestamp($pix->expires_at)->toIso8601String()
                : null,
        ];
    }

    /** Un PIX se puede reusar si tiene código y no venció. */
    private function pixVigente(?array $pix): bool
    {
        return !empty($pix['copy_paste_code'])
            && !empty($pix['expires_at'])
            && Carbon::parse($pix['expires_at'])->isFuture();
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
