<?php

namespace App\Console\Commands;

use App\Donation;
use App\Services\StripePaymentService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Stripe\Exception\ApiErrorException;

/**
 * Reconcilia donaciones puntuales que quedaron `pending` contra el estado real
 * en Stripe.
 *
 * Por qué existe: una donación puntual sólo pasa a `succeeded` cuando llega el
 * webhook `payment_intent.succeeded` (ver DonationWebhookController). PIX depende
 * 100% de eso —no hay confirmación client-side ni polling—, así que si el webhook
 * falla o se pierde (secret mal, endpoint caído, entrega no reintentada), la
 * donación queda `pending` para siempre aunque el dinero esté cobrado en Stripe.
 * Este comando es la red de seguridad: barre las `pending`, le pregunta a Stripe
 * por cada PaymentIntent y sincroniza el estado local de forma idempotente.
 *
 * Alcance: SOLO donaciones puras (`inscripcion_id IS NULL`), que son las que
 * maneja el flujo de donaciones (keys en services.stripe_donations). Las
 * donaciones ligadas a una inscripción viven bajo las keys por país
 * (StripeController) y no se tocan acá.
 *
 * Seguridad: por defecto es DRY-RUN (sólo reporta). Con `--commit` escribe.
 * Sólo transiciona sobre estados definitivos de Stripe; nunca pisa un estado
 * terminal (transitionTo lo protege).
 *
 * Ejemplos:
 *   php artisan donations:reconcile                 # dry-run, últimas pendientes
 *   php artisan donations:reconcile --commit        # aplica los cambios
 *   php artisan donations:reconcile --minutes=0 --id=pi_123  # una sola, sin filtro de edad
 */
class ReconcileDonations extends Command
{
    protected $signature = 'donations:reconcile
        {--commit : Aplica los cambios. Sin esta flag corre en dry-run (sólo reporta).}
        {--minutes=15 : Antigüedad mínima en minutos para considerar una donación (evita pisar pagos en vuelo).}
        {--limit=200 : Máximo de donaciones a revisar por corrida.}
        {--id= : Reconcilia una sola donación por su stripe_payment_intent_id (ignora el filtro de estado/edad).}';

    protected $description = 'Sincroniza donaciones pending contra Stripe (red de seguridad para PIX y webhooks perdidos).';

    /** @var StripePaymentService */
    protected $stripe;

    public function __construct(StripePaymentService $stripe)
    {
        parent::__construct();
        $this->stripe = $stripe;
    }

    public function handle(): int
    {
        $commit  = (bool) $this->option('commit');
        $minutes = (int) $this->option('minutes');
        $limit   = (int) $this->option('limit');
        $onlyId  = $this->option('id');

        $query = Donation::query()
            ->where('status', Donation::STATUS_PENDING)
            ->whereNull('inscripcion_id');

        if ($onlyId) {
            $query->where('stripe_payment_intent_id', $onlyId);
        } else {
            if ($minutes > 0) {
                $query->where('created_at', '<=', Carbon::now()->subMinutes($minutes));
            }
            $query->orderBy('created_at')->limit($limit);
        }

        $donations = $query->get();

        if ($donations->isEmpty()) {
            $this->info('No hay donaciones pending para reconciliar.');
            return 0;
        }

        $this->line(sprintf(
            '%s %d donación(es) pending%s',
            $commit ? '[COMMIT] Reconciliando' : '[DRY-RUN] Revisando',
            $donations->count(),
            $commit ? '' : ' (no se escribe nada; usá --commit para aplicar)'
        ));

        $counters = ['succeeded' => 0, 'canceled' => 0, 'failed' => 0, 'sin_cambio' => 0, 'error' => 0];
        $rows     = [];

        foreach ($donations as $donation) {
            $pi = $donation->stripe_payment_intent_id;

            try {
                $intent = $this->stripe->retrievePaymentIntent($pi, ['charges']);
            } catch (ApiErrorException $e) {
                $counters['error']++;
                $rows[] = [$donation->id, $pi, 'ERROR', 'stripe: ' . $e->getMessage()];
                continue;
            }

            [$target, $extra, $note] = $this->decide($intent);

            if ($target === null) {
                $counters['sin_cambio']++;
                $rows[] = [$donation->id, $pi, $intent->status, $note];
                continue;
            }

            $counters[$target]++;
            $rows[] = [$donation->id, $pi, $intent->status . ' → ' . $target, $note];

            if ($commit) {
                $donation->transitionTo($target, array_merge($extra, [
                    'metadata' => array_merge((array) $donation->metadata, ['stripe_status' => $intent->status, 'reconciled' => true]),
                ]));
            }
        }

        $this->table(['donation', 'payment_intent', 'transición', 'detalle'], $rows);

        $this->line(sprintf(
            'Resumen: succeeded=%d canceled=%d failed=%d sin_cambio=%d error=%d',
            $counters['succeeded'], $counters['canceled'], $counters['failed'],
            $counters['sin_cambio'], $counters['error']
        ));

        if (!$commit && ($counters['succeeded'] || $counters['canceled'] || $counters['failed'])) {
            $this->warn('Dry-run: volvé a correr con --commit para aplicar estos cambios.');
        }

        return 0;
    }

    /**
     * Decide la transición local a partir del estado real del PaymentIntent.
     *
     * @return array{0: ?string, 1: array, 2: string}  [status_destino|null, extra, nota]
     */
    private function decide(\Stripe\PaymentIntent $intent): array
    {
        switch ($intent->status) {
            case 'succeeded':
                $charge  = $intent->charges->data[0] ?? null;
                $paidTs  = $charge->created ?? $intent->created;
                $extra   = ['paid_at' => Carbon::createFromTimestamp($paidTs)];
                if (!empty($charge->receipt_url)) {
                    $extra['stripe_receipt_url'] = $charge->receipt_url;
                }
                return [Donation::STATUS_SUCCEEDED, $extra, 'pago confirmado en Stripe'];

            case 'canceled':
                return [Donation::STATUS_CANCELED, [], 'cancelado en Stripe'];

            // Intento muerto: PIX vencido o tarjeta rechazada que no se reintentó.
            // Sólo lo damos por fallido si Stripe registró un error de pago.
            case 'requires_payment_method':
            case 'requires_source':
                if ($intent->last_payment_error) {
                    $msg = $intent->last_payment_error->message ?? 'sin detalle';
                    return [Donation::STATUS_FAILED, [], 'intento fallido/vencido: ' . $msg];
                }
                return [null, [], 'esperando método de pago (aún abierto)'];

            // Todavía en vuelo: no tocar.
            default:
                // processing | requires_action | requires_confirmation | requires_capture
                return [null, [], 'en proceso, se deja pending'];
        }
    }
}
