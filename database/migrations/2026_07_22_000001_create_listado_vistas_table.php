<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Vistas guardadas de un listado configurable, por usuario y contexto.
 * Una vista persiste filtros + agrupación + columnas visibles + orden bajo un
 * nombre y color. Las vistas predefinidas NO se guardan acá: viven en código
 * (CatalogoListado::defaultViews) y se combinan en el endpoint de lectura.
 */
class CreateListadoVistasTable extends Migration
{
    public function up()
    {
        Schema::create('listado_vistas', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('persona_id');
            $table->string('list_key', 50);
            // 0 = listado sin contexto.
            $table->integer('context_id')->default(0);
            $table->string('nombre', 100);
            $table->string('color', 20)->nullable();
            // { filtros: [{campo,condicion,valor}], group_by: string|null,
            //   columnas: [keys], sort: "campo|dir" }
            $table->json('config');
            $table->integer('orden')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['persona_id', 'list_key', 'context_id'], 'listado_vistas_scope');

            $table->foreign('persona_id')
                ->references('idPersona')->on('Persona')
                ->onDelete('CASCADE');
        });
    }

    public function down()
    {
        Schema::dropIfExists('listado_vistas');
    }
}
