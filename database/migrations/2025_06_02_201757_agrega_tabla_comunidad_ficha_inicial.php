<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AgregaTablaComunidadFichaInicial extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('comunidad_ficha_inicial', function (Blueprint $table) {
            $table->increments('idFicha');
            $table->unsignedInteger('idComunidad');

            $table->foreign('idComunidad')
                ->references('idComunidad')->on('Comunidad')
                ->onDelete('CASCADE');
            $table->integer('cantidad_familias')->nullable();
            $table->integer('cantidad_viviendas')->nullable();
            $table->date('fecha_formacion')->nullable();
            $table->string('forma_constitucion')->nullable();
            $table->string('georeferencia')->nullable();
            $table->year('anio_inicio_techo')->nullable();
            $table->string('propietario_actual')->nullable();
            $table->string('estado_legalizacion')->nullable();
            $table->string('riesgo_eventos')->nullable();
            $table->string('riesgo_desalojo')->nullable();
            
            $table->json('riesgos_naturales')->nullable();
            $table->json('riesgos_antropicos')->nullable();
        
            $table->string('material_calle')->nullable();
            $table->string('acceso_electricidad')->nullable();
            $table->string('acceso_agua')->nullable();
            $table->string('manejo_aguas_residuales')->nullable();
            $table->string('manejo_aguas_pluviales')->nullable();
        
            $table->string('material_piso')->nullable();
            $table->string('material_pared')->nullable();
            $table->string('material_techo')->nullable();
            $table->string('alumbrado_publico')->nullable();
            $table->json('equipamientos')->nullable();
        
            $table->boolean('tiene_organizacion')->nullable();
            $table->boolean('liderazgos_electos')->nullable();
            $table->year('anio_eleccion')->nullable();
            $table->string('periodicidad_reunion')->nullable();
            $table->text('actividades_organizacion')->nullable();
            $table->boolean('otros_grupos')->nullable();
            $table->string('tipo_grupo')->nullable();
        
            $table->boolean('canales_comunicacion')->nullable();
            $table->string('tipo_comunicacion')->nullable();
        
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('comunidad_ficha_inicial');
    }
}
