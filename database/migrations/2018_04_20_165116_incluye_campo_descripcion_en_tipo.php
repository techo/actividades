<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class IncluyeCampoDescripcionEnTipo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('Tipo', 'descripcion')) {
            Schema::table('Tipo', function (Blueprint $table) {
                // Nullable para que sea retro compatible con pilote
                $table->text('descripcion')->nullable();
            });
            $actualizar = [
                'Detección y Asignación' => 'Es el equipo que se encarga de co-gestionar junto con los vecinos el Programa de Construcción de Viviendas de Emergencia en los barrios. Las tareas del área son encuestar a las familias de los barrios donde trabajamos con el Programa para conocer su situación, analizar las encuestas y priorizar a las familias junto a las que vamos a construir y llevar adelante la organización de la construcción junto con la mesa de trabajo del barrio o junto a un grupo promotor de vecinos.
No es necesario tener conocimientos previos. Se trabaja siempre en grupo y TECHO se encarga de capacitar a los voluntarios/as.',

                'Juegoteca' => 'Es un espacio recreativo destinado a niños de 3 a 12 años para aprender mediante el juego y el trabajo en grupo.
                Donde imaginar, crear, indagar y conocer el mundo que nos rodea es posible mediante lectura de cuentos, reciclaje, dramatización, etc. Apunta a rescatar lo lúdico como principal herramienta de aprendizaje.
                ¡No es necesario tener conocimientos previos!',

                'Apoyo Escolar' => 'Es un espacio destinado a niños de 6 a 12 años que apunta a reforzar los contenidos escolares a través del uso de cuadernillos de lengua y matemática promoviendo a su vez el trabajo en equipo y hábitos de convivencia ¡No es necesario tener conocimientos previos!',

                'Emprendedores' => 'El Programa tiene como objetivo formar y potenciar emprendedores en asentamientos informales brindando herramientas para la gestión de sus negocios, el desarrollo de diferentes habilidades y otorgando créditos bajo la metodología de Grupos Solidarios ¡No es necesario tener conocimientos previos!',

                'Capacitación en Oficios' => 'El programa de Capacitación en Oficios busca acercar oportunidades de capacitación de nivel inicial para la inserción laboral de adultos y consta de una parte técnica y una de desarrollo de habilidades para el trabajo. Los cursos tienen una duración de 4 meses con frecuencia semanal.',

                'Asesorías de Hábitat' => 'Promueve la integración social y territorial de los asentamientos informales en el marco del derecho a la ciudad a través del fortalecimiento de las capacidades comunitarias de las personas que en ellos habitan; acompañándolas y asesorándolas en la gestión de un hábitat adecuado.
Se requiere perfil de voluntario técnico particularmente enfocado en temas legales y urbanos.'

            ];
            foreach ($actualizar as $key => $value) {
                $res = DB::table('Tipo')->where('nombre', $key)->update(['descripcion' => $value]);
                echo $res . PHP_EOL;
//                \Illuminate\Support\Facades\DB::raw('UPDATE FROM Tipo SET descripcion = "' . $value . '" WHERE nombre = "'. $key .'"');
            }
        };
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Tipo', function (Blueprint $table) {
            $table->dropColumn('descripcion');
        });
    }
}
