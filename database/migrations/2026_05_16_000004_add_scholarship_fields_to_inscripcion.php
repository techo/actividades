<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds inscription-level scholarship/exemption request fields.
 *
 * These are distinct from Actividad.beca (which is just a URL to an
 * external form). These fields store the user's internal request data.
 */
class AddScholarshipFieldsToInscripcion extends Migration
{
    public function up()
    {
        Schema::table('Inscripcion', function (Blueprint $table) {
            $table->boolean('scholarship_requested')->default(false)->after('voucherUrl');
            $table->text('scholarship_reason')->nullable()->after('scholarship_requested');
            $table->string('scholarship_evidence_url')->nullable()->after('scholarship_reason');
            $table->timestamp('scholarship_requested_at')->nullable()->after('scholarship_evidence_url');
        });
    }

    public function down()
    {
        Schema::table('Inscripcion', function (Blueprint $table) {
            $table->dropColumn([
                'scholarship_requested',
                'scholarship_reason',
                'scholarship_evidence_url',
                'scholarship_requested_at',
            ]);
        });
    }
}
