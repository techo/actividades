<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddPaisIdToCampaigns extends Migration
{
    public function up()
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->integer('pais_id')->nullable()->after('oficina_id');
        });

        DB::statement('
            UPDATE campaigns c
            INNER JOIN atl_oficinas o ON o.id = c.oficina_id
            SET c.pais_id = o.id_pais
            WHERE c.oficina_id IS NOT NULL
              AND c.pais_id IS NULL
        ');
    }

    public function down()
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropColumn('pais_id');
        });
    }
}
