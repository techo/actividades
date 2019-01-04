<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TipoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("
        	INSERT INTO `Tipo` (`idTipo`, `nombre`, `hs`, `fyv`, `alias`)
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
				(26,'Arte',1,0,'art');
        	");
    }
}