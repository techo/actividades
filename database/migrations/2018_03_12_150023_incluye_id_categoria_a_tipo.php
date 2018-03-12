<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class IncluyeIdCategoriaATipo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('Tipo', 'idCategoria')) {
            //
            Schema::table('Tipo', function (Blueprint $table) {
                $table->integer('idCategoria')->unsigned()->nullable();
            });
        }
        Schema::disableForeignKeyConstraints();
        DB::table('Tipo')->truncate();
        DB::statement("INSERT INTO `Tipo` (`idTipo`, `nombre`, `hs`, `fyv`, `alias`, `idCategoria`)
            VALUES
                (1,'Plan Construcción',0,0,'cc',NULL),
                (2,'Plan Educacion',1,0,NULL,NULL),
                (3,'Plan Salud',1,0,NULL,NULL),
                (4,'Plan Microcreditos',1,0,NULL,NULL),
                (5,'Plan Juridico',1,0,NULL,NULL),
                (6,'Plan Fontecho',1,0,NULL,NULL),
                (7,'Plan Talleres electivos',1,0,NULL,NULL),
                (8,'Plan Barrios',0,0,NULL,NULL),
                (9,'Plan Equipo Construcciones',0,0,NULL,NULL),
                (10,'Plan de Ahorro',1,0,NULL,NULL),
                (11,'Detección y Asignación',0,1,'dya',1),
                (12,'Coordinador',1,0,NULL,NULL),
                (13,'Secundarios',0,0,NULL,NULL),
                (14,'Actividad Masiva',0,1,'masiva',NULL),
                (15,'Plan Urbano',1,0,NULL,NULL),
                (16,'Colecta',0,1,'colecta',NULL),
                (17,'La noche sin techo',0,1,'lnst',NULL),
                (18,'Campaña universitaria',0,1,'cuni',NULL),
                (19,'Plan Ambiental',1,0,NULL,NULL),
                (20,'Habilitación Social',0,1,'hs',NULL),
                (21,'Charla Informativa',0,1,'charla-inf',NULL),
                (22,'Juegoteca',1,0,'jue',1),
                (23,'Emprendedores',1,0,'emp',1),
                (24,'Oficios',1,0,'ofi',NULL),
                (25,'Apoyo Escolar',1,0,'apo',1),
                (26,'Arte',1,0,'art',NULL),
                (27,'Construcción',0,0,NULL,1),
                (28,'Mesa de Trabajo',0,0,NULL,1),
                (29,'Encuestamientos',0,0,NULL,1),
                (30,'Capacitación en Oficios',0,0,NULL,1),
                (31,'Asesorías de Hábitat',0,0,NULL,1),
                (32,'Infraestructura de hábitat',0,0,NULL,1),
                (33,'Charlas Informativas',0,0,NULL,2),
                (34,'Escuela de Líderes',0,0,NULL,2),
                (35,'Escuela de Gestión Comunitaria',0,0,NULL,2),
                (36,'Inducción a referentes',0,0,NULL,2),
                (37,'Seminario de Hábitat',0,0,NULL,2),
                (38,'Voluntariado',0,0,NULL,2),
                (39,'Comunicación',0,0,NULL,2),
                (40,'Desarrollo de Fondos',0,0,NULL,2),
                (41,'Legales',0,0,NULL,2),
                (42,'Trabaja en TECHO',0,0,NULL,2),
                (43,'Colecta Anual',0,0,NULL,3),
                (44,'Maratón',0,0,NULL,3),
                (45,'Seminario',0,0,NULL,3);"
        );
        Schema::enableForeignKeyConstraints();

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::disableForeignKeyConstraints();
        DB::table('Tipo')->truncate();
        Schema::table('Tipo', function (Blueprint $table) {
            $table->dropColumn(['idCategoria']);
        });
        DB::statement("INSERT INTO `Tipo` (`idTipo`, `nombre`, `hs`, `fyv`, `alias`)
            VALUES
                (1,'Plan Construcción',0,0,'cc'),
                (2,'Plan Educacion',1,0,NULL),
                (3,'Plan Salud',1,0,NULL),
                (4,'Plan Microcreditos',1,0,NULL),
                (5,'Plan Juridico',1,0,NULL),
                (6,'Plan Fontecho',1,0,NULL),
                (7,'Plan Talleres electivos',1,0,NULL),
                (8,'Plan Barrios',0,0,NULL),
                (9,'Plan Equipo Construcciones',0,0,NULL),
                (10,'Plan de Ahorro',1,0,NULL),
                (11,'Detección y Asignación',0,1,'dya'),
                (12,'Coordinador',1,0,NULL),
                (13,'Secundarios',0,0,NULL),
                (14,'Actividad Masiva',0,1,'masiva'),
                (15,'Plan Urbano',1,0,NULL),
                (16,'Colecta',0,1,'colecta'),
                (17,'La noche sin techo',0,1,'lnst'),
                (18,'Campaña universitaria',0,1,'cuni'),
                (19,'Plan Ambiental',1,0,NULL),
                (20,'Habilitación Social',0,1,'hs'),
                (21,'Charla Informativa',0,1,'charla-inf'),
                (22,'Juegoteca',1,0,'jue'),
                (23,'Emprendedores',1,0,'emp'),
                (24,'Oficios',1,0,'ofi'),
                (25,'Apoyo Escolar',1,0,'apo'),
                (26,'Arte',1,0,'art');"
        );
        Schema::enableForeignKeyConstraints();
    }
}
