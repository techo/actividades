<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddVoucherRechazadoToInscripcion extends Migration
{
    public function up()
    {
        Schema::table('Inscripcion', function (Blueprint $table) {
            $table->boolean('voucher_rechazado')->default(false)->after('voucherUrl');
            $table->text('voucher_rechazo_motivo')->nullable()->after('voucher_rechazado');
        });
    }

    public function down()
    {
        Schema::table('Inscripcion', function (Blueprint $table) {
            $table->dropColumn(['voucher_rechazado', 'voucher_rechazo_motivo']);
        });
    }
}
