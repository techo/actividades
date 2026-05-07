<?php

namespace App\Http\Controllers\api;

use App\Donation;
use App\Http\Controllers\Controller;
use App\Services\StripePaymentService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;

/**
 * Handles Stripe webhook events for the donation flow.
 *
 * Route: POST /api/donations/stripe/webhook
 * No auth middleware — Stripe signature validates the request.
 *
 * Events handled:
 *   payment_intent.succeeded       → mark donation succeeded
 *   payment_intent.payment_failed  → mark donation failed
 *   payment_intent.canceled        → mark donation canceled
 */
class DonationWebhookController extends Controller
{
    /** @var StripePaymentService */
    protected $stripe;

    public function __construct(StripePaymentService $stripe)
    {
        $this->stripe = $stripe;
    }

    public function handle(Request $request)
    {
        $rawPayload = $request->getContent();
        $sigHeader  = $request->header('Stripe-Signature', '');

        // ── 1. Validate Stripe signature ──────────────────────────────────
        try {
            $event = $this->stripe->constructWebhookEvent($rawPayload, $sigHeader);
        } catch (SignatureVerificationException $e) {
            Log::warning('DonationWebhook: invalid signature', [
                'error' => $e->getMessage(),
            ]);
            return response()->json(['message' => 'Invalid signature.'], 400);
        } catch (\RuntimeException $e) {
            // webhook_secret not configured
            Log::error('DonationWebhook: configuration error', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Webhook not configured.'], 500);
        }

        // ── 2. Route to the right handler ─────────────────────────────────
        switch ($event->type) {
            case 'payment_intent.succeeded':
                $this->handleSucceeded($event);
                break;

            case 'payment_intent.payment_failed':
                $this->handleFailed($event);
                break;

            case 'payment_intent.canceled':
                $this->handleCanceled($event);
                break;

            default:
                // Unknown event type — acknowledge so Stripe stops retrying
                Log::debug('DonationWebhook: unhandled event type', ['type' => $event->type]);
        }

        // Always return 200 to prevent Stripe from retrying events we don't handle
        return response()->json(['received' => true]);
    }

    // ── Event handlers ────────────────────────────────────────────────────

    private function handleSucceeded(\Stripe\Event $event): void
    {
        /** @var \Stripe\PaymentIntent $intent */
        $intent = $event->data->object;

        $donation = $this->findDonation($intent->id);
        if (!$donation) {
            return;
        }

        // ── Idempotency: skip if this exact event was already processed ───
        if ($donation->stripe_event_id === $event->id) {
            Log::debug('DonationWebhook: skipping duplicate event', [
                'event_id'    => $event->id,
                'donation_id' => $donation->id,
            ]);
            return;
        }

        $donation->transitionTo(Donation::STATUS_SUCCEEDED, [
            'stripe_event_id' => $event->id,
            'paid_at'         => Carbon::createFromTimestamp($event->created),
        ]);

        Log::info('DonationWebhook: donation succeeded', [
            'donation_id' => $donation->id,
            'intent_id'   => $intent->id,
        ]);
    }

    private function handleFailed(\Stripe\Event $event): void
    {
        /** @var \Stripe\PaymentIntent $intent */
        $intent = $event->data->object;

        $donation = $this->findDonation($intent->id);
        if (!$donation) {
            return;
        }

        if ($donation->stripe_event_id === $event->id) {
            return;
        }

        // Note: 'failed' is not a terminal state — the PI can be retried.
        // We mark our record for observability but don't lock out the user.
        if (!$donation->isTerminal()) {
            $donation->update([
                'status'          => Donation::STATUS_FAILED,
                'stripe_event_id' => $event->id,
            ]);
        }

        Log::info('DonationWebhook: donation payment failed', [
            'donation_id'    => $donation->id,
            'intent_id'      => $intent->id,
            'failure_message' => $intent->last_payment_error->message ?? null,
        ]);
    }

    private function handleCanceled(\Stripe\Event $event): void
    {
        /** @var \Stripe\PaymentIntent $intent */
        $intent = $event->data->object;

        $donation = $this->findDonation($intent->id);
        if (!$donation) {
            return;
        }

        if ($donation->stripe_event_id === $event->id) {
            return;
        }

        $donation->transitionTo(Donation::STATUS_CANCELED, [
            'stripe_event_id' => $event->id,
        ]);

        Log::info('DonationWebhook: donation canceled', [
            'donation_id' => $donation->id,
            'intent_id'   => $intent->id,
        ]);
    }

    // ── Private helpers ───────────────────────────────────────────────────

    private function findDonation(string $paymentIntentId): ?Donation
    {
        $donation = Donation::where('stripe_payment_intent_id', $paymentIntentId)->first();

        if (!$donation) {
            Log::warning('DonationWebhook: unknown PaymentIntent', [
                'intent_id' => $paymentIntentId,
            ]);
        }

        return $donation;
    }
}
