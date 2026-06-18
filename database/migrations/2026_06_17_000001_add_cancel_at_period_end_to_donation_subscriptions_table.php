<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds `cancel_at_period_end` to donation_subscriptions.
 *
 * Stripe sets this flag when a subscription is scheduled to cancel at the end
 * of the current billing period (e.g. the user cancels via the Customer Portal
 * but keeps access until the period ends). The subscription stays `active`
 * until then, so this flag is the only way the app can tell the difference
 * between "active and renewing" and "active but ending soon".
 *
 * Additive and backwards-compatible: existing rows default to false.
 */
class AddCancelAtPeriodEndToDonationSubscriptionsTable extends Migration
{
    public function up()
    {
        Schema::table('donation_subscriptions', function (Blueprint $table) {
            $table->boolean('cancel_at_period_end')
                ->default(false)
                ->after('current_period_end');
        });
    }

    public function down()
    {
        Schema::table('donation_subscriptions', function (Blueprint $table) {
            $table->dropColumn('cancel_at_period_end');
        });
    }
}
