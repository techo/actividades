<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Tipo de documento elegido por la persona (Fase 2 de documentos).
 *
 * Hasta ahora el documento se guardaba en `dni` como texto libre y el tipo se
 * AUTO-DETECTABA probando los tipos aceptados del país en orden (y cayendo a
 * pasaporte). Con un tipo explícito la validación pasa a ser ESTRICTA contra un
 * solo formato (mejora seguridad/calidad) y la ficha de seguro sabe exactamente
 * qué documento es.
 *
 *  - tipo_documento (string): key del tipo en config/documentos.php
 *    ('dni_ar', 'cpf', 'rut', 'pasaporte', 'generico', ...).
 *
 * Nullable: las filas legacy no tienen tipo (se siguen auto-detectando). No se
 * backfillea para no inventar un tipo que la persona no eligió.
 */
class AddTipoDocumentoToPersona extends Migration
{
    public function up()
    {
        Schema::table('Persona', function (Blueprint $table) {
            $table->string('tipo_documento', 30)->nullable()->after('dni');
        });
    }

    public function down()
    {
        Schema::table('Persona', function (Blueprint $table) {
            $table->dropColumn('tipo_documento');
        });
    }
}
