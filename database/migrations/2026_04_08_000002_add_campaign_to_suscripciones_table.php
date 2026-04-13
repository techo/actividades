<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCampaignToSuscripcionesTable extends Migration
{
    public function up()
    {
        Schema::table('Suscripciones', function (Blueprint $table) {
            $table->integer('campaign_id')->unsigned()->nullable()->index()->after('instagram');
            $table->boolean('convertido')->default(false)->after('campaign_id');

            $table->foreign('campaign_id')
                ->references('id')->on('campaigns')
                ->onDelete('SET NULL');
        });
    }

    public function down()
    {
        Schema::table('Suscripciones', function (Blueprint $table) {
            $table->dropForeign(['campaign_id']);
            $table->dropColumn(['campaign_id', 'convertido']);
        });
    }
}
