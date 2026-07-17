<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateListadoColumnaValoresTable extends Migration
{
    public function up()
    {
        Schema::create('listado_columna_valores', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('columna_id');
            // PK del registro del listado (idInscripcion, idIntegrante...);
            // la semántica la define el list_key de la columna padre.
            $table->integer('record_id');
            // Un solo TEXT para todos los tipos: casilla "1"/"0", fecha "Y-m-d",
            // persona idPersona, etiquetas array JSON, texto/estado literal.
            $table->text('valor')->nullable();
            $table->integer('updated_by');
            $table->timestamps();

            $table->unique(['columna_id', 'record_id'], 'listado_columna_valores_unique');

            // El soft delete de la columna conserva los valores; solo el force delete cascadea.
            $table->foreign('columna_id')
                ->references('id')->on('listado_columnas')
                ->onDelete('CASCADE');
        });
    }

    public function down()
    {
        Schema::dropIfExists('listado_columna_valores');
    }
}
