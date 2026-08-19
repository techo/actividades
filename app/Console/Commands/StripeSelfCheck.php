<?php

namespace App\Console\Commands;

use App\Pais;
use App\Services\StripePaymentService;
use Illuminate\Console\Command;
use Stripe\Account;
use Stripe\Customer;
use Stripe\PaymentIntent;
use Stripe\PaymentMethod;
use Stripe\Stripe;
use Stripe\Subscription;
use Stripe\Webhook;

/**
 * Chequeo end-to-end de la integración con Stripe.
 *
 * Objetivo: poder afirmar, con un solo comando, que TODO el lado servidor de
 * Stripe funciona (keys bien configuradas + cuenta + capacidades + webhooks).
 * Si este comando pasa en verde, cualquier error de pago que reporte un usuario
 * es del lado de la app/cliente, no del backend ni de la configuración.
 *
 * Cubre, contra la API real de Stripe en modo test:
 *   - Donación puntual con tarjeta (create estilo app + cobro confirmado)
 *   - Donación mensual (suscripción recurrente, primer cobro confirmado)
 *   - Pago de inscripción por actividad, por país (tarjeta)
 *   - PIX para Brasil (generación de QR)
 *   - Validación del tipo de cada key (detecta pk_ puesta en un campo secret)
 *   - Verificación de firma de webhooks (donaciones + por país), opcional HTTP real
 *
 * Seguridad: por defecto se NIEGA a hacer cobros contra keys `*_live` (usa
 * `--allow-live` para forzarlo). Los objetos de prueba creados se limpian al
 * final salvo `--keep`.
 *
 * Ejemplos:
 *   php artisan stripe:selfcheck
 *   php artisan stripe:selfcheck --only=enrollment --country=br
 *   php artisan stripe:selfcheck --http-webhook --json
 */
class StripeSelfCheck extends Command
{
    protected $signature = 'stripe:selfcheck
        {--country= : Limita las pruebas de inscripción a un país (iso2 o id). Por defecto: todos los payment_class=Stripe}
        {--only= : donations|enrollment — corre solo ese bloque (por defecto ambos)}
        {--no-charge : No confirma cobros; solo valida keys y creación de intents}
        {--allow-live : Permite cobrar contra keys live (por defecto se niega)}
        {--http-webhook : Además del check de firma in-process, hace un POST real al endpoint de webhook}
        {--keep : No limpia los objetos de prueba creados en Stripe}
        {--json : Salida en JSON en vez de tabla legible}';

    protected $description = 'Chequeo end-to-end de Stripe: donación puntual/mensual, inscripción por actividad, tarjeta, PIX y webhooks contra las keys configuradas.';

    /** Payment method de prueba de Stripe (tarjeta Visa que siempre aprueba). */
    const TEST_PM = 'pm_card_visa';

    /** Monto de prueba en unidades menores (15.00). */
    const AMOUNT = 1500;

    /** @var array<int, array{scope:string,check:string,status:string,detail:string}> */
    private $rows = [];

    /** @var array<int, array{type:string,id:string,key:string}> objetos a limpiar */
    private $cleanup = [];

    private $failures = 0;
    private $warnings = 0;
    private $passes   = 0;

    /** Moneda ISO por país (misma fuente de verdad que los controllers de inscripción). */
    private $monedaPorPais = [
        'ar' => 'ars', 'bo' => 'bob', 'br' => 'brl', 'co' => 'cop', 'cr' => 'crc',
        'do' => 'dop', 'ec' => 'usd', 'sv' => 'usd', 'gt' => 'gtq', 'hn' => 'hnl',
        'mx' => 'mxn', 'pa' => 'usd', 'py' => 'pyg', 'pe' => 'pen', 'uy' => 'uyu',
        've' => 'usd',
    ];

    public function handle()
    {
        $only = $this->option('only');

        $this->line('');
        $this->line('══ Stripe self-check ══  APP_ENV=' . config('app.env') . '  ' . now()->toDateTimeString());

        if (!$only || $only === 'donations') {
            $this->checkDonations();
        }
        if (!$only || $only === 'enrollment') {
            $this->checkEnrollment();
        }

        $this->runCleanup();

        if ($this->option('json')) {
            $this->line(json_encode([
                'env'      => config('app.env'),
                'summary'  => ['pass' => $this->passes, 'fail' => $this->failures, 'warn' => $this->warnings],
                'rows'     => $this->rows,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        } else {
            $this->render();
        }

        return $this->failures > 0 ? 1 : 0;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // DONACIONES  (key desde config services.stripe_donations — cuenta única)
    // ─────────────────────────────────────────────────────────────────────────
    private function checkDonations()
    {
        $scope  = 'Donaciones';
        $secret = config('services.stripe_donations.secret');
        $whsec  = config('services.stripe_donations.webhook_secret');

        $kind = $this->keyKind($secret);
        if (!$this->assertSecretKind($scope, 'tipo de secret key', $kind)) {
            return; // sin secret usable no tiene sentido seguir
        }
        $this->assertWebhookSecret($scope, 'webhook secret', $whsec);

        // Creación + cobros: solo si está permitido (bloqueado en live sin --allow-live).
        if ($this->chargesAllowed($scope, $kind)) {
            $this->donationCharges($scope, $secret);
        }

        // ── Webhook (firma) — siempre: es verificación local, no cobra ─────────
        $this->webhookSignature(
            $scope,
            $whsec,
            function ($payload, $sig) {
                return app(StripePaymentService::class)->constructWebhookEvent($payload, $sig);
            },
            '/api/donations/stripe/webhook'
        );
    }

    /** Creación + cobros de donaciones (puntual + mensual). Requiere secret usable. */
    private function donationCharges($scope, $secret)
    {
        Stripe::setApiKey($secret);

        // Moneda soportada por la cuenta (evita fallos por moneda no habilitada
        // y de paso prueba que la key puede leer la cuenta).
        try {
            $currency = Account::retrieve()->default_currency ?: 'usd';
            $this->pass($scope, 'lectura de cuenta', 'default_currency=' . strtoupper($currency));
        } catch (\Exception $e) {
            $this->fail($scope, 'lectura de cuenta', $this->err($e));
            return;
        }

        // Puntual: creación estilo app (automatic_payment_methods)
        try {
            $pi = PaymentIntent::create([
                'amount'                    => self::AMOUNT,
                'currency'                  => $currency,
                'automatic_payment_methods' => ['enabled' => true],
                'metadata'                  => ['selfcheck' => '1', 'flow' => 'donation_oneshot'],
            ], ['idempotency_key' => uniqid('sc_don_create_')]);
            $ok = !empty($pi->client_secret);
            $this->record($scope, 'puntual · create (flujo app)', $ok ? 'ok' : 'fail',
                $ok ? $pi->id . ' · client_secret ✓' : 'sin client_secret');
        } catch (\Exception $e) {
            $this->fail($scope, 'puntual · create (flujo app)', $this->err($e));
        }

        if (!$this->option('no-charge')) {
            $this->cardCharge($scope, 'puntual · cobro tarjeta', $currency);
            $this->subscriptionCharge($scope, $currency);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // INSCRIPCIONES POR ACTIVIDAD  (key desde atl_pais.config_pago — por país)
    // ─────────────────────────────────────────────────────────────────────────
    private function checkEnrollment()
    {
        $filtro = $this->option('country');

        $paises = Pais::whereNotNull('config_pago')->where('config_pago', '<>', '')->get()
            ->filter(function ($p) {
                $cfg = json_decode($p->config_pago);
                return $cfg && ($cfg->payment_class ?? null) === 'Stripe';
            })
            ->filter(function ($p) use ($filtro) {
                if (!$filtro) return true;
                return strcasecmp($p->iso2, $filtro) === 0 || (string) $p->id === (string) $filtro;
            });

        if ($paises->isEmpty()) {
            $this->warn2('Inscripciones', 'países Stripe', 'no se encontró ningún país con payment_class=Stripe' . ($filtro ? " para '$filtro'" : ''));
            return;
        }

        foreach ($paises as $pais) {
            $scope = 'Inscripción · ' . $pais->nombre . ' (' . strtolower($pais->iso2) . ')';
            $cfg   = json_decode($pais->config_pago);

            $secret = $cfg->stripe_secret ?? null;
            $whsec  = $cfg->stripe_webhook_secret ?? null;
            $public = $cfg->stripe_public ?? null;

            $kind = $this->keyKind($secret);
            if (!$this->assertSecretKind($scope, 'secret key', $kind)) {
                continue;
            }
            $this->assertWebhookSecret($scope, 'webhook secret', $whsec);
            $this->assertPublicKey($scope, $public, $kind);

            $iso = strtolower($pais->iso2 ?? '');

            // Creación + cobros: solo si está permitido (bloqueado en live sin --allow-live).
            if ($this->chargesAllowed($scope, $kind)) {
                Stripe::setApiKey($secret);
                $currency = $this->monedaPorPais[$iso] ?? 'usd';

                // ── Create de PaymentIntent estilo InscripcionStripeController ─
                try {
                    $pi = PaymentIntent::create([
                        'amount'                    => self::AMOUNT,
                        'currency'                  => $currency,
                        'automatic_payment_methods' => ['enabled' => true],
                        'metadata'                  => ['selfcheck' => '1', 'flow' => 'inscripcion', 'pais_id' => $pais->id],
                    ], ['idempotency_key' => uniqid('sc_ins_create_')]);
                    $ok = !empty($pi->client_secret);
                    $this->record($scope, 'PI create (flujo app)', $ok ? 'ok' : 'fail',
                        $ok ? $pi->id . ' · ' . strtoupper($currency) : 'sin client_secret');
                } catch (\Exception $e) {
                    $this->fail($scope, 'PI create (flujo app)', $this->err($e));
                }

                // ── Cobro con tarjeta ─────────────────────────────────────────
                if (!$this->option('no-charge')) {
                    $this->cardCharge($scope, 'cobro tarjeta', $currency);
                }

                // ── PIX (solo Brasil) ─────────────────────────────────────────
                if ($iso === 'br' && !$this->option('no-charge')) {
                    $this->pixCharge($scope, $secret);
                }
            }

            // ── Webhook (firma) por país — siempre (local, no cobra) ──────────
            $key = $secret;
            $this->webhookSignature(
                $scope,
                $whsec,
                function ($payload, $sig) use ($key, $whsec) {
                    Stripe::setApiKey($key);
                    return Webhook::constructEvent($payload, $sig, $whsec);
                },
                '/stripe/webhook/' . $pais->id
            );
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Primitivas de cobro
    // ─────────────────────────────────────────────────────────────────────────

    /** Crea y confirma un PaymentIntent con tarjeta de test → debe quedar succeeded. */
    private function cardCharge($scope, $check, $currency)
    {
        try {
            $pi = PaymentIntent::create([
                'amount'               => self::AMOUNT,
                'currency'             => $currency,
                'payment_method'       => self::TEST_PM,
                'payment_method_types' => ['card'],
                'confirm'              => true,
                'metadata'             => ['selfcheck' => '1'],
            ], ['idempotency_key' => uniqid('sc_card_')]);

            $ok = $pi->status === 'succeeded';
            $this->record($scope, $check, $ok ? 'ok' : 'fail',
                'status=' . $pi->status . ' · ' . strtoupper($currency) . ' ' . number_format(self::AMOUNT / 100, 2));
        } catch (\Exception $e) {
            $this->fail($scope, $check, $this->err($e));
        }
    }

    /** Crea customer + suscripción recurrente (vía el service real) y confirma el primer cobro. */
    private function subscriptionCharge($scope, $currency)
    {
        $check = 'mensual · suscripción';
        try {
            $customer = Customer::create([
                'email'    => 'selfcheck+' . uniqid() . '@techo.org',
                'metadata' => ['selfcheck' => '1'],
            ]);
            $this->cleanup[] = ['type' => 'customer', 'id' => $customer->id, 'key' => Stripe::getApiKey()];

            /** @var StripePaymentService $svc */
            $svc = app(StripePaymentService::class);
            $sub = $svc->createSubscription(
                $customer->id, self::AMOUNT, $currency, 'month', 0, 'profile', uniqid('sc_sub_')
            );
            $this->cleanup[] = ['type' => 'subscription', 'id' => $sub->id, 'key' => Stripe::getApiKey()];

            // Confirmar el primer PaymentIntent de la factura (default_incomplete).
            $piId = $sub->latest_invoice->payment_intent->id ?? null;
            if ($piId) {
                $pi = PaymentIntent::retrieve($piId);
                if ($pi->status !== 'succeeded') {
                    $pi = $pi->confirm(['payment_method' => self::TEST_PM]);
                }
            }

            $sub = Subscription::retrieve($sub->id);
            $ok  = in_array($sub->status, ['active', 'trialing'], true);
            $this->record($scope, $check, $ok ? 'ok' : 'fail', 'status=' . $sub->status . ' · ' . $sub->id);
        } catch (\Exception $e) {
            $this->fail($scope, $check, $this->err($e));
        }
    }

    /** Crea un PaymentIntent PIX y confirma → debe generar el QR (requires_action). */
    private function pixCharge($scope, $secret)
    {
        $check = 'PIX · generación de QR';
        try {
            Stripe::setApiKey($secret);
            $pi = PaymentIntent::create([
                'amount'               => self::AMOUNT,
                'currency'             => 'brl',
                'payment_method_types' => ['pix'],
                'payment_method_data'  => [
                    'type'            => 'pix',
                    'billing_details' => [
                        'name'   => 'Self Check TECHO',
                        'email'  => 'selfcheck@techo.org',
                        'tax_id' => '11144477735', // CPF de prueba (BR exige tax_id para PIX)
                    ],
                ],
                'confirm'              => true,
                'metadata'             => ['selfcheck' => '1'],
            ], ['idempotency_key' => uniqid('sc_pix_')]);

            $this->cleanup[] = ['type' => 'payment_intent', 'id' => $pi->id, 'key' => $secret];

            $hasQr = isset($pi->next_action->pix_display_qr_code);
            // 'requires_source_action' es el alias en API versions viejas de 'requires_action'.
            $ok    = in_array($pi->status, ['requires_action', 'requires_source_action'], true) && $hasQr;
            $this->record($scope, $check, $ok ? 'ok' : 'fail',
                'status=' . $pi->status . ($hasQr ? ' · QR ✓' : ' · sin QR'));
        } catch (\Exception $e) {
            // Error típico: PIX no habilitado en la cuenta / país.
            $this->fail($scope, $check, $this->err($e));
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Webhooks
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Verifica la firma de webhook: firma un evento benigno con el whsec y comprueba
     * que el verificador lo acepta (in-process). Además, control negativo con firma
     * inválida, y opcionalmente un POST HTTP real al endpoint.
     */
    private function webhookSignature($scope, $whsec, callable $verifier, $endpointPath)
    {
        if (strpos((string) $whsec, 'whsec_') !== 0) {
            // Ya reportado por assertWebhookSecret; no repetir la creación de firma.
            return;
        }

        // Evento benigno: 'payment_intent.created' no está en ningún switch de
        // los handlers → firma válida sin efectos secundarios.
        $payload = json_encode([
            'id'   => 'evt_selfcheck_' . uniqid(),
            'object' => 'event',
            'type' => 'payment_intent.created',
            'data' => ['object' => ['id' => 'pi_selfcheck', 'object' => 'payment_intent']],
        ]);
        $t   = time();
        $sig = 't=' . $t . ',v1=' . hash_hmac('sha256', $t . '.' . $payload, $whsec);

        try {
            $verifier($payload, $sig);
            // Control negativo: una firma mal formada DEBE ser rechazada.
            $rejected = false;
            try {
                $verifier($payload, 't=' . $t . ',v1=deadbeef');
            } catch (\Exception $e) {
                $rejected = true;
            }
            $this->record($scope, 'webhook · firma', $rejected ? 'ok' : 'fail',
                $rejected ? 'válida acepta / inválida rechaza' : 'no rechazó firma inválida (!)');
        } catch (\Exception $e) {
            $this->fail($scope, 'webhook · firma', $this->err($e));
        }

        if ($this->option('http-webhook')) {
            $this->webhookHttp($scope, $payload, $sig, $endpointPath);
        }
    }

    /** POST real del payload firmado al endpoint local → debe responder 200. */
    private function webhookHttp($scope, $payload, $sig, $endpointPath)
    {
        $url = rtrim(config('app.url'), '/') . $endpointPath;
        try {
            $client = new \GuzzleHttp\Client(['verify' => false, 'http_errors' => false, 'timeout' => 20]);
            $resp = $client->post($url, [
                'body'    => $payload,
                'headers' => [
                    'Stripe-Signature' => $sig,
                    'Content-Type'     => 'application/json',
                ],
            ]);
            $code = $resp->getStatusCode();
            $ok   = $code === 200;
            $this->record($scope, 'webhook · HTTP endpoint', $ok ? 'ok' : 'fail', 'HTTP ' . $code . ' ' . $endpointPath);
        } catch (\Exception $e) {
            $this->fail($scope, 'webhook · HTTP endpoint', $this->err($e));
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Validaciones de keys
    // ─────────────────────────────────────────────────────────────────────────

    private function keyKind($key)
    {
        if (!$key) return 'missing';
        foreach (['sk_live_' => 'secret_live', 'rk_live_' => 'restricted_live',
                  'sk_test_' => 'secret_test', 'rk_test_' => 'restricted_test',
                  'pk_live_' => 'publishable_live', 'pk_test_' => 'publishable_test'] as $prefix => $kind) {
            if (strpos($key, $prefix) === 0) return $kind;
        }
        return 'unknown';
    }

    private function isSecretKind($kind)
    {
        return in_array($kind, ['secret_live', 'restricted_live', 'secret_test', 'restricted_test'], true);
    }

    private function isLive($kind)
    {
        return strpos($kind, '_live') !== false;
    }

    /** Devuelve true si la key es un secret usable. Reporta el resultado. */
    private function assertSecretKind($scope, $check, $kind)
    {
        if ($kind === 'missing') {
            $this->fail($scope, $check, 'no configurada');
            return false;
        }
        if (!$this->isSecretKind($kind)) {
            // El bug clásico: una publishable key (pk_) guardada en el campo secret.
            $this->fail($scope, $check, "es una publishable key ($kind) — debe ser sk_/rk_. Las llamadas server-side van a fallar.");
            return false;
        }
        $mode = $this->isLive($kind) ? 'LIVE' : 'test';
        $this->pass($scope, $check, str_replace('_', ' ', $kind) . " ($mode)");
        return true;
    }

    private function assertWebhookSecret($scope, $check, $whsec)
    {
        if (!$whsec) {
            $this->fail($scope, $check, 'no configurado');
            return;
        }
        if (strpos($whsec, 'whsec_') !== 0) {
            $this->fail($scope, $check, 'formato inesperado (debe empezar con whsec_)');
            return;
        }
        $this->pass($scope, $check, 'whsec_ ✓');
    }

    /** La publishable key es opcional en el server, pero la app la necesita. */
    private function assertPublicKey($scope, $public, $secretKind)
    {
        if (!$public) {
            $this->warn2($scope, 'publishable key', 'falta stripe_public en config_pago — la app móvil la necesita para confirmar el pago');
            return;
        }
        $pk = $this->keyKind($public);
        if (strpos($pk, 'publishable') !== 0) {
            $this->fail($scope, 'publishable key', "el valor de stripe_public no es una pk_ ($pk)");
            return;
        }
        // Debe coincidir el modo (test/live) con el secret.
        if ($this->isLive($pk) !== $this->isLive($secretKind)) {
            $this->fail($scope, 'publishable key', 'mezcla test/live: la pk no coincide con el modo del secret');
            return;
        }
        $this->pass($scope, 'publishable key', 'pk ✓ (mismo modo que el secret)');
    }

    /** Decide si se permiten cobros/creación de intents (bloquea live sin --allow-live). */
    private function chargesAllowed($scope, $kind)
    {
        if ($this->isLive($kind) && !$this->option('allow-live')) {
            $this->record($scope, 'cobros (live)', 'skip', 'key LIVE — cobros omitidos (validé keys y webhooks; usá --allow-live para cobrar)');
            return false;
        }
        return true;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Limpieza
    // ─────────────────────────────────────────────────────────────────────────
    private function runCleanup()
    {
        if ($this->option('keep') || empty($this->cleanup)) {
            return;
        }
        foreach (array_reverse($this->cleanup) as $obj) {
            try {
                Stripe::setApiKey($obj['key']);
                switch ($obj['type']) {
                    case 'subscription':
                        Subscription::retrieve($obj['id'])->cancel();
                        break;
                    case 'customer':
                        Customer::retrieve($obj['id'])->delete();
                        break;
                    case 'payment_intent':
                        $pi = PaymentIntent::retrieve($obj['id']);
                        if (in_array($pi->status, ['requires_payment_method', 'requires_confirmation', 'requires_action', 'processing'], true)) {
                            $pi->cancel();
                        }
                        break;
                }
            } catch (\Exception $e) {
                // Limpieza best-effort; no rompe el resultado del chequeo.
            }
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Registro / salida
    // ─────────────────────────────────────────────────────────────────────────
    private function record($scope, $check, $status, $detail)
    {
        if ($status === 'ok')   $this->passes++;
        if ($status === 'fail') $this->failures++;
        if ($status === 'warn') $this->warnings++;
        $this->rows[] = compact('scope', 'check', 'status', 'detail');
    }

    private function pass($scope, $check, $detail)  { $this->record($scope, $check, 'ok', $detail); }
    private function fail($scope, $check, $detail)  { $this->record($scope, $check, 'fail', $detail); }
    private function warn2($scope, $check, $detail) { $this->record($scope, $check, 'warn', $detail); }

    private function err(\Exception $e)
    {
        $msg = $e->getMessage();
        return strlen($msg) > 160 ? substr($msg, 0, 157) . '...' : $msg;
    }

    private function render()
    {
        $icons = ['ok' => '✓', 'fail' => '✗', 'warn' => '!', 'skip' => '·'];
        $lastScope = null;
        foreach ($this->rows as $r) {
            if ($r['scope'] !== $lastScope) {
                $this->line('');
                $this->line('▸ ' . $r['scope']);
                $lastScope = $r['scope'];
            }
            $icon = $icons[$r['status']] ?? '?';
            $this->line(sprintf('   %s  %-28s %s', $icon, $r['check'], $r['detail']));
        }
        $this->line('');
        $this->line(str_repeat('─', 60));
        $this->line(sprintf('RESUMEN: %d OK · %d FALLAS · %d avisos', $this->passes, $this->failures, $this->warnings));
        $this->line($this->failures === 0
            ? '✓ Stripe (server-side) OK. Si un pago falla, el problema está en la app/cliente.'
            : '✗ Hay fallas de configuración/servidor de Stripe — revisar arriba antes de culpar a la app.');
        $this->line('');
    }
}
