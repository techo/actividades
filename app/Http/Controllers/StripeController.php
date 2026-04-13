<?php

namespace App\Http\Controllers;

use App\Inscripcion;
use App\Mail\MailInscripcionConfirmada;
use App\Mail\MailInscripcionPagoFueraDeFecha;
use App\Pais;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class StripeController extends Controller
{
    /**
     * Crea una Stripe Checkout Session y redirige al usuario.
     */
    public function createCheckout(Request $request, $idInscripcion)
    {
        $inscripcion = Inscripcion::where('idInscripcion', $idInscripcion)
            ->with(['actividad.pais', 'persona'])
            ->firstOrFail();

        if ($inscripcion->idPersona !== auth()->user()->idPersona) {
            abort(403);
        }

        if ($inscripcion->pago == 1) {
            return view('inscripciones.pagada', [
                'inscripcion' => $inscripcion,
                'actividad'   => $inscripcion->actividad,
            ]);
        }

        $config = json_decode($inscripcion->actividad->pais->config_pago);

        if (empty($config->stripe_secret)) {
            abort(404, 'Este país no tiene Stripe configurado.');
        }

        \Stripe\Stripe::setApiKey($config->stripe_secret);

        $actividad     = $inscripcion->actividad;
        $montoCentavos = (int) round((float) $actividad->montoMin * 100);

        if ($montoCentavos <= 0) {
            abort(400, 'El monto de pago no está configurado correctamente.');
        }

        try {
            $session = \Stripe\Checkout\Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency'     => strtolower($actividad->moneda ?: 'ars'),
                        'unit_amount'  => $montoCentavos,
                        'product_data' => [
                            'name'        => $actividad->nombreActividad,
                            'description' => 'Inscripción #' . $idInscripcion,
                        ],
                    ],
                    'quantity' => 1,
                ]],
                'mode'               => 'payment',
                'success_url'        => url('/stripe/success') . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url'         => url('/stripe/cancel/' . $idInscripcion),
                'client_reference_id' => (string) $idInscripcion,
                'customer_email'     => $inscripcion->persona->mail,
                'metadata' => [
                    'inscripcion_id' => $idInscripcion,
                    'pais_id'        => $actividad->pais->id,
                ],
            ]);
        } catch (\Stripe\Exception\ApiErrorException $e) {
            Log::error('Stripe: error al crear sesión para inscripcion ' . $idInscripcion . ': ' . $e->getMessage());
            return back()->with('error', __('frontend.stripe_error'));
        }

        $inscripcion->stripe_session_id = $session->id;
        $inscripcion->save();

        return redirect($session->url);
    }

    /**
     * Stripe redirige aquí tras un pago exitoso.
     * Sólo muestra confirmación — la fuente de verdad es el webhook.
     */
    public function success(Request $request)
    {
        $sessionId = $request->get('session_id');

        if (!$sessionId) {
            return redirect('/');
        }

        $inscripcion = Inscripcion::where('stripe_session_id', $sessionId)
            ->with(['actividad'])
            ->first();

        if (!$inscripcion) {
            return redirect('/')->with('info', __('frontend.stripe_payment_pending'));
        }

        // Si el webhook ya procesó el pago, mostrar vista de confirmada
        if ($inscripcion->pago == 1) {
            return view('inscripciones.pagada', [
                'inscripcion' => $inscripcion,
                'actividad'   => $inscripcion->actividad,
            ]);
        }

        // El webhook puede tardar unos segundos
        return view('stripe.pending', [
            'inscripcion' => $inscripcion,
            'actividad'   => $inscripcion->actividad,
        ]);
    }

    /**
     * El usuario canceló el pago en Stripe.
     */
    public function cancel($idInscripcion)
    {
        $inscripcion = Inscripcion::where('idInscripcion', $idInscripcion)
            ->with(['actividad'])
            ->first();

        return view('stripe.cancel', [
            'inscripcion' => $inscripcion,
            'actividad'   => $inscripcion ? $inscripcion->actividad : null,
        ]);
    }

    /**
     * Webhook de Stripe. Sin CSRF ni autenticación de sesión.
     * La seguridad se basa en la firma del header Stripe-Signature.
     * URL: /stripe/webhook/{paisId}
     */
    public function webhook(Request $request, $paisId)
    {
        $pais = Pais::find($paisId);

        if (!$pais) {
            Log::warning('Stripe webhook: país ' . $paisId . ' no encontrado.');
            return response('Not found', 404);
        }

        $config = json_decode($pais->config_pago);

        if (empty($config->stripe_webhook_secret) || empty($config->stripe_secret)) {
            Log::error('Stripe webhook: país ' . $paisId . ' no tiene Stripe configurado correctamente.');
            return response('Misconfigured', 500);
        }

        $payload   = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');

        \Stripe\Stripe::setApiKey($config->stripe_secret);

        try {
            $event = \Stripe\Webhook::constructEvent($payload, $sigHeader, $config->stripe_webhook_secret);
        } catch (\UnexpectedValueException $e) {
            Log::warning('Stripe webhook país ' . $paisId . ': payload inválido - ' . $e->getMessage());
            return response('Invalid payload', 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            Log::warning('Stripe webhook país ' . $paisId . ': firma inválida - ' . $e->getMessage());
            return response('Invalid signature', 400);
        }

        Log::info('Stripe webhook recibido [país ' . $paisId . ']: ' . $event->type);

        if ($event->type === 'checkout.session.completed') {
            $this->handleCheckoutCompleted($event->data->object);
        }

        return response('OK', 200);
    }

    /**
     * Procesa el evento checkout.session.completed.
     * Diseñado para ser idempotente.
     */
    protected function handleCheckoutCompleted($session)
    {
        $inscripcionId = isset($session->metadata->inscripcion_id)
            ? $session->metadata->inscripcion_id
            : $session->client_reference_id;

        if (!$inscripcionId) {
            Log::error('Stripe webhook: no se encontró inscripcion_id. Session: ' . $session->id);
            return;
        }

        $inscripcion = Inscripcion::where('idInscripcion', $inscripcionId)
            ->with(['actividad', 'persona'])
            ->first();

        if (!$inscripcion) {
            Log::error('Stripe webhook: inscripcion ' . $inscripcionId . ' no encontrada.');
            return;
        }

        // Idempotencia: no procesar dos veces
        if ($inscripcion->pago == 1 && $inscripcion->metodo_pago === 'stripe') {
            Log::info('Stripe webhook: inscripcion ' . $inscripcionId . ' ya procesada. Ignorando.');
            return;
        }

        if ($session->payment_status !== 'paid') {
            Log::warning('Stripe webhook: payment_status "' . $session->payment_status . '" para session ' . $session->id);
            return;
        }

        $actividad = $inscripcion->actividad;

        // Verificar fecha límite de pago
        if ($actividad->fechaLimitePago && Carbon::now()->greaterThan($actividad->fechaLimitePago)) {
            Log::warning('Stripe webhook: pago fuera de fecha para inscripcion ' . $inscripcionId);
            try {
                Mail::to($inscripcion->persona->mail)->queue(new MailInscripcionPagoFueraDeFecha($inscripcion));
            } catch (\Exception $e) {
                Log::error('Stripe: error enviando mail fuera de fecha para inscripcion ' . $inscripcionId . ': ' . $e->getMessage());
            }
            return;
        }

        $inscripcion->pago                     = 1;
        $inscripcion->montoPago                = $session->amount_total / 100;
        $inscripcion->moneda                   = strtoupper($session->currency);
        $inscripcion->fechaPago                = Carbon::now();
        $inscripcion->metodo_pago              = 'stripe';
        $inscripcion->stripe_payment_intent_id = $session->payment_intent;
        $inscripcion->save();

        Log::info('Stripe: inscripcion ' . $inscripcionId . ' marcada como pagada. PI: ' . $session->payment_intent);

        try {
            Mail::to($inscripcion->persona->mail)->queue(new MailInscripcionConfirmada($inscripcion));
        } catch (\Exception $e) {
            Log::error('Stripe: error enviando mail de confirmación para inscripcion ' . $inscripcionId . ': ' . $e->getMessage());
        }
    }
}
