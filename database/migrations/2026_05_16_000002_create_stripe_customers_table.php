<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Maps each Persona to a Stripe Customer ID for the donations flow.
 *
 * Kept separate from atl_personas to avoid touching the main user table.
 * One row per person — unique on both sides to prevent duplicate customers.
 */
class CreateStripeCustomersTable extends Migration
{
    public function up()
    {
        Schema::create('stripe_customers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('person_id')->unique();
            $table->string('stripe_customer_id')->unique();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('stripe_customers');
    }
}
