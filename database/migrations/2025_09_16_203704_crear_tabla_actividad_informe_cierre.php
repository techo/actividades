<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CrearTablaActividadInformeCierre extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('actividad_informe_cierre', function (Blueprint $table) {
            $table->increments('idActividadInformeCierre'); // primary key
            $table->integer('idActividad'); // relación con Actividad
            $table->integer('numero_participantes')->nullable(); // Número de vecinas y vecinos
            $table->string('programa')->nullable(); // Programa en el cual se puede enmarcar
            $table->json('soluciones_entregadas')->nullable(); // Listado de soluciones entregadas
            $table->integer('numero_beneficiados')->nullable(); // Número de personas beneficiadas
            $table->integer('cant_soluciones_voluntariado')->nullable(); 
            $table->integer('cant_soluciones_corporativos')->nullable(); 
            $table->integer('cant_soluciones_secundarios')->nullable(); 
            $table->integer('cant_soluciones_universitarios')->nullable(); 
            $table->integer('cant_soluciones_familias')->nullable();
            $table->string('quienes_financiaron')->nullable(); // Quiénes financiaron el proyecto
            $table->string('archivos_adicionales')->nullable(); // Archivos adicionales
            $table->string('comentarios_adicionales')->nullable(); // Comentarios adicionales
            $table->timestamps();

            // Foreign key
            $table->foreign('idActividad')
                  ->references('idActividad')
                  ->on('Actividad')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('actividad_informe_cierre');
    }
}
