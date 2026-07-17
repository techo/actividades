<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateListadoPreferenciasTable extends Migration
{
    public function up()
    {
        Schema::create('listado_preferencias', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('persona_id');
            $table->string('list_key', 50);
            // 0 = listado sin contexto (ej. suscriptos globales)
            $table->integer('context_id')->default(0);
            // Array JSON ordenado de keys de columnas visibles: ["dni", "pregunta_12", "custom_5"]
            $table->json('columnas');
            $table->timestamps();

            $table->unique(['persona_id', 'list_key', 'context_id'], 'listado_preferencias_unique');

            $table->foreign('persona_id')
                ->references('idPersona')->on('Persona')
                ->onDelete('CASCADE');
        });
    }

    public function down()
    {
        Schema::dropIfExists('listado_preferencias');
    }
}
