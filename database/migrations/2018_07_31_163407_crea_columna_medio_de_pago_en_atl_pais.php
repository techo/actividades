<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreaColumnaMedioDePagoEnAtlPais extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('atl_pais', 'medio_pago')) {
            Schema::table('atl_pais', function (Blueprint $table) {
                $table->string('medio_pago')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('atl_pais', function (Blueprint $table) {
            $table->dropColumn('medio_pago');
        });
    }
}
