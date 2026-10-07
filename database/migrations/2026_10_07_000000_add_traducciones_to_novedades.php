<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Novedades por idioma: `texto` queda como texto base (fallback) y `traducciones` guarda
 * {locale: texto} (es_AR, es_CH, es, pt, en). `locales` restringe a quién se muestra
 * (ej. ["pt"] para algo solo de Brasil); null = a todos.
 */
class AddTraduccionesToNovedades extends Migration
{
    public function up()
    {
        Schema::table('novedades', function (Blueprint $table) {
            $table->json('traducciones')->nullable()->after('texto');
            $table->json('locales')->nullable()->after('traducciones');
        });
    }

    public function down()
    {
        Schema::table('novedades', function (Blueprint $table) {
            $table->dropColumn(['traducciones', 'locales']);
        });
    }
}
