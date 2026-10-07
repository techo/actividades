<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Plataforma afectada por el reporte: web (actividades.techo.org / backoffice), app
 * (MiTECHO) o ambas. El reporte se levanta siempre desde el backoffice web, así que el
 * dato NO se puede inferir del navegador: lo declara la persona o lo corrige el admin en
 * el triage. Nullable = "sin especificar".
 */
class AddPlatformToIssueReports extends Migration
{
    public function up()
    {
        Schema::table('issue_reports', function (Blueprint $table) {
            $table->string('platform', 10)->nullable()->after('area'); // web | app | ambas
            $table->index('platform');
        });
    }

    public function down()
    {
        Schema::table('issue_reports', function (Blueprint $table) {
            $table->dropIndex(['platform']);
            $table->dropColumn('platform');
        });
    }
}
