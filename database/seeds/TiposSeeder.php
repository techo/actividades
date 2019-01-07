<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TiposSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
		DB::statement("INSERT INTO `Tipo` (`idTipo`, `nombre`, `hs`, `fyv`, `alias`, `idCategoria`,`flujo`)
            VALUES
                (1,'Plan Construcción',0,0,'cc',NULL,'GENERICO'),
                (2,'Plan Educacion',1,0,NULL,NULL,'GENERICO'),
                (3,'Plan Salud',1,0,NULL,NULL,'GENERICO'),
                (4,'Plan Microcreditos',1,0,NULL,NULL,'GENERICO'),
                (5,'Plan Juridico',1,0,NULL,NULL,'GENERICO'),
                (6,'Plan Fontecho',1,0,NULL,NULL,'GENERICO'),
                (7,'Plan Talleres electivos',1,0,NULL,NULL,'GENERICO'),
                (8,'Plan Barrios',0,0,NULL,NULL,'GENERICO'),
                (9,'Plan Equipo Construcciones',0,0,NULL,NULL,'GENERICO'),
                (10,'Plan de Ahorro',1,0,NULL,NULL,'GENERICO'),
                (11,'Detección y Asignación',0,1,'dya',1,'GENERICO'),
                (12,'Coordinador',1,0,NULL,NULL,'GENERICO'),
                (13,'Secundarios',0,0,NULL,NULL,'GENERICO'),
                (14,'Actividad Masiva',0,1,'masiva',NULL,'GENERICO'),
                (15,'Plan Urbano',1,0,NULL,NULL,'GENERICO'),
                (16,'Colecta',0,1,'colecta',NULL,'GENERICO'),
                (17,'La noche sin techo',0,1,'lnst',NULL,'GENERICO'),
                (18,'Campaña universitaria',0,1,'cuni',NULL,'GENERICO'),
                (19,'Plan Ambiental',1,0,NULL,NULL,'GENERICO'),
                (20,'Habilitación Social',0,1,'hs',NULL,'GENERICO'),
                (21,'Charla Informativa',0,1,'charla-inf',NULL,'GENERICO'),
                (22,'Juegoteca',1,0,'jue',1,'GENERICO'),
                (23,'Emprendedores',1,0,'emp',1,'GENERICO'),
                (24,'Oficios',1,0,'ofi',NULL,'GENERICO'),
                (25,'Apoyo Escolar',1,0,'apo',1,'GENERICO'),
                (26,'Arte',1,0,'art',NULL,'GENERICO'),
                (27,'Construcción',0,0,NULL,1,'CONSTRUCCION'),
                (28,'Mesa de Trabajo',0,0,NULL,1,'GENERICO'),
                (29,'Encuestamientos',0,0,NULL,1,'GENERICO'),
                (30,'Capacitación en Oficios',0,0,NULL,1,'GENERICO'),
                (31,'Asesorías de Hábitat',0,0,NULL,1,'GENERICO'),
                (32,'Infraestructura de hábitat',0,0,NULL,1,'GENERICO'),
                (33,'Charlas Informativas',0,0,NULL,2,'GENERICO'),
                (34,'Actividades de Formación',0,0,NULL,2,'GENERICO'),
                (35,'Capacitaciones',0,0,NULL,2,'GENERICO'),
                (36,'Inducción a referentes',0,0,NULL,2,'GENERICO'),
                (43,'Colecta Anual',0,0,NULL,3,'GENERICO'),
                (44,'Maratón',0,0,NULL,3,'GENERICO'),
                (45,'Seminario',0,0,NULL,3,'GENERICO');"
        );

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
            }

    }
}