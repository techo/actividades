<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateListadoColumnasTable extends Migration
{
    public function up()
    {
        Schema::create('listado_columnas', function (Blueprint $table) {
            $table->increments('id');
            $table->string('list_key', 50);
            $table->integer('context_id')->default(0);
            $table->string('nombre', 100);
            // casilla | estado | etiquetas | texto | fecha | persona (validado en el Request)
            $table->string('tipo', 20);
            // Para estado/etiquetas: array JSON de strings. Inmutable post-creación.
            $table->json('opciones')->nullable();
            $table->integer('orden')->default(0);
            $table->integer('created_by');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['list_key', 'context_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('listado_columnas');
    }
}
