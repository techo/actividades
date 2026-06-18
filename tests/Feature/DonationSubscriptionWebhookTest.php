<?php

namespace Tests\Feature;

use App\DonationSubscription;
use App\Services\StripePaymentService;
use App\StripeCustomer;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Mockery;
use Tests\TestCase;

/**
 * Feature coverage for the donation subscription additions:
 *  - POST /api/donations/stripe/billing-portal  (200 with customer, 404 without)
 *  - customer.subscription.updated webhook: syncs amount + cancel_at_period_end,
 *    and the terminal guard (does NOT revive a canceled subscription).
 *  - customer.subscription.deleted webhook: marks canceled + canceled_at.
 *
 * StripePaymentService is mocked so the tests never call the live Stripe API.
 */
class DonationSubscriptionWebhookTest extends TestCase
{
    use RefreshDatabase;

    private function persona()
    {
        return factory('App\Persona')->create();
    }

    private function makeSubscription($persona, array $overrides = []): DonationSubscription
    {
        return DonationSubscription::create(array_merge([
            'person_id'              => $persona->idPersona,
            'stripe_subscription_id' => 'sub_test_123',
            'stripe_customer_id'     => 'cus_test_123',
            'amount'                 => 1000,
            'currency'               => 'usd',
            'interval'               => 'month',
            'status'                 => DonationSubscription::STATUS_INCOMPLETE,
            'source'                 => 'profile',
        ], $overrides));
    }

    /** Build a mocked StripePaymentService that returns the given event from constructWebhookEvent. */
    private function mockWebhook(\Stripe\Event $event): void
    {
        $mock = Mockery::mock(StripePaymentService::class);
        $mock->shouldReceive('constructWebhookEvent')->andReturn($event);
        $this->app->instance(StripePaymentService::class, $mock);
    }

    private function subscriptionEvent(string $type, array $object): \Stripe\Event
    {
        return \Stripe\Event::constructFrom([
            'id'      => 'evt_' . uniqid(),
            'object'  => 'event',
            'type'    => $type,
            'created' => time(),
            'data'    => ['object' => array_merge([
                'id'     => 'sub_test_123',
                'object' => 'subscription',
            ], $object)],
        ]);
    }

    // ── billing-portal ────────────────────────────────────────────────────────

    /** @test */
    public function billing_portal_returns_404_when_user_has_no_stripe_customer()
    {
        $persona = $this->persona();
        Passport::actingAs($persona);

        $this->postJson('/api/donations/stripe/billing-portal')
            ->assertStatus(404)
            ->assertJsonStructure(['message']);
    }

    /** @test */
    public function billing_portal_returns_200_and_url_when_user_has_a_stripe_customer()
    {
        $persona = $this->persona();
        StripeCustomer::create([
            'person_id'          => $persona->idPersona,
            'stripe_customer_id' => 'cus_test_123',
        ]);

        $mock = Mockery::mock(StripePaymentService::class);
        $mock->shouldReceive('createBillingPortalSession')
            ->once()
            ->with('cus_test_123', 'mitecho://stripe-billing-portal-return')
            ->andReturn('https://billing.stripe.com/p/session/test_abc');
        $this->app->instance(StripePaymentService::class, $mock);

        Passport::actingAs($persona);

        $this->postJson('/api/donations/stripe/billing-portal')
            ->assertStatus(200)
            ->assertJson(['url' => 'https://billing.stripe.com/p/session/test_abc']);
    }

    // ── customer.subscription.updated ───────────────────────────────────────────

    /** @test */
    public function subscription_updated_syncs_status_amount_and_cancel_at_period_end()
    {
        $persona = $this->persona();
        $sub     = $this->makeSubscription($persona);

        $periodEnd = 1815000000; // far-future epoch
        $event = $this->subscriptionEvent('customer.subscription.updated', [
            'status'               => 'active',
            'cancel_at_period_end' => true,
            'current_period_end'   => $periodEnd,
            'items'                => ['object' => 'list', 'data' => [
                ['id' => 'si_x', 'price' => ['unit_amount' => 2500]],
            ]],
        ]);
        $this->mockWebhook($event);

        $this->postJson('/api/donations/stripe/webhook')->assertStatus(200);

        $sub->refresh();
        $this->assertSame(DonationSubscription::STATUS_ACTIVE, $sub->status);
        $this->assertSame(2500, $sub->amount);
        $this->assertTrue((bool) $sub->cancel_at_period_end);
        $this->assertEquals(
            Carbon::createFromTimestamp($periodEnd)->timestamp,
            $sub->current_period_end->timestamp
        );
    }

    /** @test */
    public function subscription_updated_does_not_revive_a_terminal_subscription()
    {
        $persona = $this->persona();
        $sub     = $this->makeSubscription($persona, [
            'status' => DonationSubscription::STATUS_CANCELED,
            'amount' => 2500,
        ]);

        // A late / out-of-order "updated" event trying to set it active again.
        $event = $this->subscriptionEvent('customer.subscription.updated', [
            'status'               => 'active',
            'cancel_at_period_end' => false,
            'current_period_end'   => 1820000000,
            'items'                => ['object' => 'list', 'data' => [
                ['id' => 'si_x', 'price' => ['unit_amount' => 9999]],
            ]],
        ]);
        $this->mockWebhook($event);

        $this->postJson('/api/donations/stripe/webhook')->assertStatus(200);

        $sub->refresh();
        // Terminal guard: nothing changes.
        $this->assertSame(DonationSubscription::STATUS_CANCELED, $sub->status);
        $this->assertSame(2500, $sub->amount);
    }

    // ── customer.subscription.deleted ───────────────────────────────────────────

    /** @test */
    public function subscription_deleted_marks_canceled_with_canceled_at()
    {
        $persona = $this->persona();
        $sub     = $this->makeSubscription($persona, [
            'status' => DonationSubscription::STATUS_ACTIVE,
        ]);

        $canceledAt = 1781720000;
        $event = $this->subscriptionEvent('customer.subscription.deleted', [
            'status'       => 'canceled',
            'canceled_at'  => $canceledAt,
        ]);
        $this->mockWebhook($event);

        $this->postJson('/api/donations/stripe/webhook')->assertStatus(200);

        $sub->refresh();
        $this->assertSame(DonationSubscription::STATUS_CANCELED, $sub->status);
        $this->assertNotNull($sub->canceled_at);
        $this->assertEquals($canceledAt, $sub->canceled_at->timestamp);
    }
}
