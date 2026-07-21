<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Agrega el tipo 'archivo' al enum `tipo` de campaign_preguntas.
 *
 * Doctrine no maneja enums de MySQL, así que se altera con SQL crudo.
 */
class AddArchivoToCampaignPreguntasTipo extends Migration
{
    public function up()
    {
        DB::statement("ALTER TABLE campaign_preguntas MODIFY COLUMN tipo ENUM('abierta','desplegable','archivo') NOT NULL DEFAULT 'abierta'");
    }

    public function down()
    {
        DB::statement("ALTER TABLE campaign_preguntas MODIFY COLUMN tipo ENUM('abierta','desplegable') NOT NULL DEFAULT 'abierta'");
    }
}
