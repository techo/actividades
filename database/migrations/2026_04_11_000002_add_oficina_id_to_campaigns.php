<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOficinaIdToCampaigns extends Migration
{
    public function up()
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->unsignedInteger('oficina_id')->nullable()->after('imagen');
            $table->foreign('oficina_id')->references('id')->on('atl_oficinas')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropForeign(['oficina_id']);
            $table->dropColumn('oficina_id');
        });
    }
}
