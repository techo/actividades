<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AgregaTagsEvaluacionActividad extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       Schema::table('EvaluacionActividad', function (Blueprint $table) {
            $table->json('tags_positivos')->nullable();
            $table->json('tags_negativos')->nullable();
        });
    }

    public function down()
    {
        Schema::table('EvaluacionActividad', function (Blueprint $table) {
            $table->dropColumn(['tags_positivos']);
            $table->dropColumn(['tags_negativos']);
        });
    }
}