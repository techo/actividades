<?php

namespace App\Http\Controllers\api;

use App\Donation;
use App\DonationSubscription;
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
 * PaymentIntent events (one-time donations):
 *   payment_intent.succeeded       → mark donation succeeded
 *   payment_intent.payment_failed  → mark donation failed
 *   payment_intent.canceled        → mark donation canceled
 *
 * Subscription events (recurring donations):
 *   invoice.paid                    → mark subscription active, update period
 *   invoice.payment_failed          → mark subscription past_due
 *   customer.subscription.updated   → sync status and billing period
 *   customer.subscription.deleted   → mark subscription canceled
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
            Log::error('DonationWebhook: configuration error', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Webhook not configured.'], 500);
        }

        // ── 2. Route to the right handler ─────────────────────────────────
        switch ($event->type) {

            // ── One-time donation events ──────────────────────────────────
            case 'payment_intent.succeeded':
                $this->handlePaymentIntentSucceeded($event);
                break;

            case 'payment_intent.payment_failed':
                $this->handlePaymentIntentFailed($event);
                break;

            case 'payment_intent.canceled':
                $this->handlePaymentIntentCanceled($event);
                break;

            // ── Recurring subscription events ─────────────────────────────
            case 'invoice.paid':
                $this->handleInvoicePaid($event);
                break;

            case 'invoice.payment_failed':
                $this->handleInvoicePaymentFailed($event);
                break;

            case 'customer.subscription.updated':
                $this->handleSubscriptionUpdated($event);
                break;

            case 'customer.subscription.deleted':
                $this->handleSubscriptionDeleted($event);
                break;

            default:
                Log::debug('DonationWebhook: unhandled event type', ['type' => $event->type]);
        }

        // Always return 200 — prevents Stripe from retrying unhandled events
        return response()->json(['received' => true]);
    }

    // =========================================================================
    // One-time PaymentIntent handlers
    // =========================================================================

    private function handlePaymentIntentSucceeded(\Stripe\Event $event): void
    {
        /** @var \Stripe\PaymentIntent $intent */
        $intent   = $event->data->object;
        $donation = $this->findDonation($intent->id);

        if (!$donation) {
            return;
        }

        if ($donation->stripe_event_id === $event->id) {
            Log::debug('DonationWebhook: skipping duplicate event', [
                'event_id'    => $event->id,
                'donation_id' => $donation->id,
            ]);
            return;
        }

        // Receipt URL lives on the charge, not the PaymentIntent itself.
        // (API 2020-08-27 exposes charges.data[] inline on the PI.)
        $extra = [
            'stripe_event_id' => $event->id,
            'paid_at'         => Carbon::createFromTimestamp($event->created),
        ];
        $receiptUrl = $intent->charges->data[0]->receipt_url ?? null;
        if ($receiptUrl) {
            $extra['stripe_receipt_url'] = $receiptUrl;
        }

        $donation->transitionTo(Donation::STATUS_SUCCEEDED, $extra);

        Log::info('DonationWebhook: donation succeeded', [
            'donation_id' => $donation->id,
            'intent_id'   => $intent->id,
        ]);
    }

    private function handlePaymentIntentFailed(\Stripe\Event $event): void
    {
        /** @var \Stripe\PaymentIntent $intent */
        $intent   = $event->data->object;
        $donation = $this->findDonation($intent->id);

        if (!$donation || $donation->stripe_event_id === $event->id) {
            return;
        }

        // 'failed' is non-terminal — the PI can be retried by the user
        if (!$donation->isTerminal()) {
            $donation->update([
                'status'          => Donation::STATUS_FAILED,
                'stripe_event_id' => $event->id,
            ]);
        }

        Log::info('DonationWebhook: donation payment failed', [
            'donation_id'     => $donation->id,
            'intent_id'       => $intent->id,
            'failure_message' => $intent->last_payment_error->message ?? null,
        ]);
    }

    private function handlePaymentIntentCanceled(\Stripe\Event $event): void
    {
        /** @var \Stripe\PaymentIntent $intent */
        $intent   = $event->data->object;
        $donation = $this->findDonation($intent->id);

        if (!$donation || $donation->stripe_event_id === $event->id) {
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

    // =========================================================================
    // Recurring subscription handlers
    // =========================================================================

    /**
     * invoice.paid — a subscription invoice was paid successfully.
     * Mark the subscription active and update the billing period.
     */
    private function handleInvoicePaid(\Stripe\Event $event): void
    {
        /** @var \Stripe\Invoice $invoice */
        $invoice = $event->data->object;

        $subscriptionId = $invoice->subscription ?? null;
        if (!$subscriptionId) {
            return;
        }

        $sub = $this->findSubscription($subscriptionId);
        if (!$sub || $sub->stripe_event_id === $event->id) {
            return;
        }

        $sub->transitionTo(DonationSubscription::STATUS_ACTIVE, [
            'stripe_event_id'    => $event->id,
            'current_period_end' => $invoice->lines->data[0]->period->end ?? null
                ? Carbon::createFromTimestamp($invoice->lines->data[0]->period->end)
                : $sub->current_period_end,
        ]);

        Log::info('DonationWebhook: subscription invoice paid', [
            'subscription_id' => $subscriptionId,
            'invoice_id'      => $invoice->id,
        ]);
    }

    /**
     * invoice.payment_failed — a subscription invoice could not be collected.
     * Stripe will retry automatically; we mark the sub as past_due for the app.
     */
    private function handleInvoicePaymentFailed(\Stripe\Event $event): void
    {
        /** @var \Stripe\Invoice $invoice */
        $invoice = $event->data->object;

        $subscriptionId = $invoice->subscription ?? null;
        if (!$subscriptionId) {
            return;
        }

        $sub = $this->findSubscription($subscriptionId);
        if (!$sub || $sub->stripe_event_id === $event->id) {
            return;
        }

        if (!$sub->isTerminal()) {
            $sub->update([
                'status'          => DonationSubscription::STATUS_PAST_DUE,
                'stripe_event_id' => $event->id,
            ]);
        }

        Log::info('DonationWebhook: subscription invoice payment failed', [
            'subscription_id' => $subscriptionId,
            'invoice_id'      => $invoice->id,
        ]);
    }

    /**
     * customer.subscription.updated — sync any status or period changes.
     * Stripe fires this on plan changes, trial ends, status transitions, etc.
     */
    private function handleSubscriptionUpdated(\Stripe\Event $event): void
    {
        /** @var \Stripe\Subscription $stripeSubscription */
        $stripeSubscription = $event->data->object;

        $sub = $this->findSubscription($stripeSubscription->id);
        if (!$sub || $sub->stripe_event_id === $event->id) {
            return;
        }

        // Don't resurrect a subscription we've already marked terminal
        // (canceled / incomplete_expired). A late, out-of-order `updated`
        // event must not overwrite a final state set by `deleted`.
        if ($sub->isTerminal()) {
            return;
        }

        // Sync status, billing period and amount from Stripe's source of truth.
        $updates = [
            'stripe_event_id'      => $event->id,
            'status'               => $stripeSubscription->status,
            'cancel_at_period_end' => (bool) $stripeSubscription->cancel_at_period_end,
        ];

        $item = $stripeSubscription->items->data[0] ?? null;

        // `current_period_end` lives on the subscription object in API 2020-08-27,
        // but newer default API versions moved it onto each subscription item.
        // Read whichever Stripe sent so the sync works regardless of the API
        // version this webhook was delivered with.
        $periodEnd = $stripeSubscription->current_period_end
            ?? ($item->current_period_end ?? null);
        if ($periodEnd) {
            $updates['current_period_end'] = Carbon::createFromTimestamp($periodEnd);
        }

        // Amount can change if the user switches plans from the Customer Portal.
        // Read it from the first subscription item's price (fallback to plan).
        $stripeAmount = $item->price->unit_amount
            ?? $item->plan->amount
            ?? null;
        if ($stripeAmount !== null) {
            $updates['amount'] = (int) $stripeAmount;
        }

        $sub->update($updates);

        Log::info('DonationWebhook: subscription updated', [
            'subscription_id' => $stripeSubscription->id,
            'new_status'      => $stripeSubscription->status,
        ]);
    }

    /**
     * customer.subscription.deleted — the subscription has been canceled
     * (either by the user, by Stripe due to failed payments, or via the dashboard).
     */
    private function handleSubscriptionDeleted(\Stripe\Event $event): void
    {
        /** @var \Stripe\Subscription $stripeSubscription */
        $stripeSubscription = $event->data->object;

        $sub = $this->findSubscription($stripeSubscription->id);
        if (!$sub || $sub->stripe_event_id === $event->id) {
            return;
        }

        $sub->transitionTo(DonationSubscription::STATUS_CANCELED, [
            'stripe_event_id' => $event->id,
            'canceled_at'     => $stripeSubscription->canceled_at
                ? Carbon::createFromTimestamp($stripeSubscription->canceled_at)
                : Carbon::now(),
        ]);

        Log::info('DonationWebhook: subscription canceled/deleted', [
            'subscription_id' => $stripeSubscription->id,
        ]);
    }

    // ── Private helpers ───────────────────────────────────────────────────────

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

    private function findSubscription(string $subscriptionId): ?DonationSubscription
    {
        $sub = DonationSubscription::where('stripe_subscription_id', $subscriptionId)->first();

        if (!$sub) {
            Log::warning('DonationWebhook: unknown Subscription', [
                'subscription_id' => $subscriptionId,
            ]);
        }

        return $sub;
    }
}
