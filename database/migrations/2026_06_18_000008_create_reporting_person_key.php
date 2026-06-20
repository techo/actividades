<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Fase 4 — Pseudonimización: person_key surrogate.
 *
 * reporting_person (idPersona -> person_key UUID) es la tabla de re-identificación
 * y NO se expone a Power BI. Todas las vistas reporting_* que exponían idPersona
 * pasan a exponer person_key, de modo que un consumidor de analytics no ve el
 * identificador real de la persona. Los joins entre vistas siguen funcionando por
 * person_key (1:1 con idPersona).
 *
 * Las personas nuevas reciben su key con el comando reporting:sync-person-keys
 * (programado a diario en App\Console\Kernel). up() expone person_key; down()
 * revierte las vistas a idPersona y elimina el mapeo.
 */
class CreateReportingPersonKey extends Migration
{
    public function up()
    {
        Schema::create('reporting_person', function (Blueprint $table) {
            $table->unsignedInteger('idPersona')->primary();
            $table->char('person_key', 36)->unique();
            $table->timestamp('created_at')->nullable();
        });

        DB::statement('INSERT INTO reporting_person (idPersona, person_key, created_at)
                       SELECT idPersona, UUID(), NOW() FROM Persona');

        DB::statement("
            CREATE OR REPLACE VIEW reporting_fact_participacion AS
            SELECT i.idInscripcion, i.idActividad, rp.person_key,
                   a.idPais, a.idOficina, t.tipo_indicador,
                   a.fechaInicio AS fecha_actividad, YEAR(a.fechaInicio) AS anio, MONTH(a.fechaInicio) AS mes,
                   CASE WHEN i.presente=1 THEN 1 ELSE 0 END AS es_presente,
                   CASE WHEN i.presente=1 THEN 1 ELSE 0 END AS es_movilizado,
                   CASE WHEN i.presente=1 AND t.tipo_indicador IN ('territorio','construccion_de_viviendas') THEN 1 ELSE 0 END AS es_kpi
            FROM Inscripcion i
            JOIN Actividad a ON a.idActividad=i.idActividad AND a.deleted_at IS NULL
            LEFT JOIN Tipo t ON t.idTipo=a.idTipo
            LEFT JOIN reporting_person rp ON rp.idPersona=i.idPersona
            WHERE i.deleted_at IS NULL
        ");

        DB::statement("
            CREATE OR REPLACE VIEW reporting_fact_membresia AS
            SELECT ig.idIntegrante, rp.person_key, ig.idEquipo,
                   e.idOficina, e.idPais, e.area_id, ig.idComunidad, ig.rol,
                   ig.fechaInicio, ig.fechaFin,
                   CASE WHEN ig.fechaFin IS NULL OR ig.fechaFin > NOW() THEN 1 ELSE 0 END AS vigente
            FROM Integrantes ig
            JOIN Equipo e ON e.idEquipo=ig.idEquipo AND e.deleted_at IS NULL
            LEFT JOIN reporting_person rp ON rp.idPersona=ig.idPersona
            WHERE ig.deleted_at IS NULL
        ");

        DB::statement("
            CREATE OR REPLACE VIEW reporting_fact_evaluacion_actividad AS
            SELECT ea.idEvaluacion, ea.idActividad, rp.person_key,
                   a.idPais, a.idOficina, t.tipo_indicador,
                   a.fechaInicio AS fecha_actividad, YEAR(a.fechaInicio) AS anio,
                   ea.puntaje, ea.tags_positivos, ea.tags_negativos,
                   CASE WHEN ea.comentario IS NOT NULL AND ea.comentario <> '' THEN 1 ELSE 0 END AS tiene_comentario
            FROM EvaluacionActividad ea
            JOIN Actividad a ON a.idActividad=ea.idActividad AND a.deleted_at IS NULL
            LEFT JOIN Tipo t ON t.idTipo=a.idTipo
            LEFT JOIN reporting_person rp ON rp.idPersona=ea.idPersona
        ");

        DB::statement("
            CREATE OR REPLACE VIEW reporting_fact_evaluacion_impacto AS
            SELECT ei.idEvaluacionImpacto, ei.idActividad, rp.person_key,
                   a.idPais, a.idOficina, a.fechaInicio AS fecha_actividad, YEAR(a.fechaInicio) AS anio,
                   ei.impacto_habilidades_capacidades, ei.impacto_percepcion_realidad, ei.impacto_recomendaria_experiencia,
                   CASE WHEN ei.impacto_recomendaria_experiencia>=9 THEN 1 ELSE 0 END AS es_promotor,
                   CASE WHEN ei.impacto_recomendaria_experiencia<=6 THEN 1 ELSE 0 END AS es_detractor,
                   CASE WHEN ei.impacto_recomendaria_experiencia>=9 THEN 'promotor'
                        WHEN ei.impacto_recomendaria_experiencia<=6 THEN 'detractor' ELSE 'pasivo' END AS nps_categoria
            FROM EvaluacionImpactoActividad ei
            JOIN Actividad a ON a.idActividad=ei.idActividad AND a.deleted_at IS NULL
            LEFT JOIN reporting_person rp ON rp.idPersona=ei.idPersona
        ");

        DB::statement("
            CREATE OR REPLACE VIEW reporting_dim_persona AS
            SELECT rp.person_key, p.genero,
                   CASE
                       WHEN p.fechaNacimiento IS NULL THEN NULL
                       WHEN TIMESTAMPDIFF(YEAR, p.fechaNacimiento, CURDATE()) BETWEEN 18 AND 21 THEN '18 a 21'
                       WHEN TIMESTAMPDIFF(YEAR, p.fechaNacimiento, CURDATE()) BETWEEN 22 AND 25 THEN '22 a 25'
                       ELSE '26 o más'
                   END AS rango_edad,
                   p.canal_contacto, p.idPais, p.idProvincia, p.idLocalidad
            FROM Persona p
            JOIN reporting_person rp ON rp.idPersona=p.idPersona
            WHERE p.deleted_at IS NULL
        ");

        DB::statement("
            CREATE OR REPLACE VIEW reporting_fact_lifecycle AS
            SELECT rp.person_key, p.idPais,
                   COALESCE(mb.algun_vigente,0) AS es_integrante_vigente,
                   COALESCE(mb.ex_integrante,0) AS fue_integrante,
                   COALESCE(pr.tiene_presencia,0) AS tiene_presencia,
                   COALESCE(su.es_suscripto,0) AS es_suscripto,
                   CASE
                       WHEN COALESCE(mb.algun_vigente,0)=1 THEN 'transformacion'
                       WHEN COALESCE(mb.ex_integrante,0)=1 THEN 'cierre'
                       WHEN COALESCE(pr.tiene_presencia,0)=1 THEN 'insercion'
                       WHEN COALESCE(su.es_suscripto,0)=1 THEN 'captado'
                       ELSE 'sin_etapa'
                   END AS etapa
            FROM Persona p
            JOIN reporting_person rp ON rp.idPersona=p.idPersona
            LEFT JOIN (SELECT person_key AS pk, MAX(vigente) AS algun_vigente,
                              MAX(CASE WHEN vigente=0 THEN 1 ELSE 0 END) AS ex_integrante
                       FROM reporting_fact_membresia GROUP BY person_key) mb ON mb.pk = rp.person_key
            LEFT JOIN (SELECT person_key AS pk, 1 AS tiene_presencia
                       FROM reporting_fact_participacion WHERE es_presente=1 GROUP BY person_key) pr ON pr.pk = rp.person_key
            LEFT JOIN (SELECT rp2.person_key AS pk, 1 AS es_suscripto
                       FROM Suscripciones s JOIN reporting_person rp2 ON rp2.idPersona=s.idPersona
                       WHERE s.idPersona IS NOT NULL GROUP BY rp2.person_key) su ON su.pk = rp.person_key
            WHERE p.deleted_at IS NULL
        ");
    }

    public function down()
    {
        // Revierte las vistas a exponer idPersona (estado previo a Fase 4).
        DB::statement("
            CREATE OR REPLACE VIEW reporting_fact_participacion AS
            SELECT i.idInscripcion, i.idActividad, i.idPersona,
                   a.idPais, a.idOficina, t.tipo_indicador,
                   a.fechaInicio AS fecha_actividad, YEAR(a.fechaInicio) AS anio, MONTH(a.fechaInicio) AS mes,
                   CASE WHEN i.presente=1 THEN 1 ELSE 0 END AS es_presente,
                   CASE WHEN i.presente=1 THEN 1 ELSE 0 END AS es_movilizado,
                   CASE WHEN i.presente=1 AND t.tipo_indicador IN ('territorio','construccion_de_viviendas') THEN 1 ELSE 0 END AS es_kpi
            FROM Inscripcion i
            JOIN Actividad a ON a.idActividad=i.idActividad AND a.deleted_at IS NULL
            LEFT JOIN Tipo t ON t.idTipo=a.idTipo
            WHERE i.deleted_at IS NULL
        ");
        DB::statement("
            CREATE OR REPLACE VIEW reporting_fact_membresia AS
            SELECT ig.idIntegrante, ig.idPersona, ig.idEquipo,
                   e.idOficina, e.idPais, e.area_id, ig.idComunidad, ig.rol,
                   ig.fechaInicio, ig.fechaFin,
                   CASE WHEN ig.fechaFin IS NULL OR ig.fechaFin > NOW() THEN 1 ELSE 0 END AS vigente
            FROM Integrantes ig
            JOIN Equipo e ON e.idEquipo=ig.idEquipo AND e.deleted_at IS NULL
            WHERE ig.deleted_at IS NULL
        ");
        DB::statement("
            CREATE OR REPLACE VIEW reporting_fact_evaluacion_actividad AS
            SELECT ea.idEvaluacion, ea.idActividad, ea.idPersona,
                   a.idPais, a.idOficina, t.tipo_indicador,
                   a.fechaInicio AS fecha_actividad, YEAR(a.fechaInicio) AS anio,
                   ea.puntaje, ea.tags_positivos, ea.tags_negativos,
                   CASE WHEN ea.comentario IS NOT NULL AND ea.comentario <> '' THEN 1 ELSE 0 END AS tiene_comentario
            FROM EvaluacionActividad ea
            JOIN Actividad a ON a.idActividad=ea.idActividad AND a.deleted_at IS NULL
            LEFT JOIN Tipo t ON t.idTipo=a.idTipo
        ");
        DB::statement("
            CREATE OR REPLACE VIEW reporting_fact_evaluacion_impacto AS
            SELECT ei.idEvaluacionImpacto, ei.idActividad, ei.idPersona,
                   a.idPais, a.idOficina, a.fechaInicio AS fecha_actividad, YEAR(a.fechaInicio) AS anio,
                   ei.impacto_habilidades_capacidades, ei.impacto_percepcion_realidad, ei.impacto_recomendaria_experiencia,
                   CASE WHEN ei.impacto_recomendaria_experiencia>=9 THEN 1 ELSE 0 END AS es_promotor,
                   CASE WHEN ei.impacto_recomendaria_experiencia<=6 THEN 1 ELSE 0 END AS es_detractor,
                   CASE WHEN ei.impacto_recomendaria_experiencia>=9 THEN 'promotor'
                        WHEN ei.impacto_recomendaria_experiencia<=6 THEN 'detractor' ELSE 'pasivo' END AS nps_categoria
            FROM EvaluacionImpactoActividad ei
            JOIN Actividad a ON a.idActividad=ei.idActividad AND a.deleted_at IS NULL
        ");
        DB::statement("
            CREATE OR REPLACE VIEW reporting_dim_persona AS
            SELECT p.idPersona, p.genero,
                   CASE
                       WHEN p.fechaNacimiento IS NULL THEN NULL
                       WHEN TIMESTAMPDIFF(YEAR, p.fechaNacimiento, CURDATE()) BETWEEN 18 AND 21 THEN '18 a 21'
                       WHEN TIMESTAMPDIFF(YEAR, p.fechaNacimiento, CURDATE()) BETWEEN 22 AND 25 THEN '22 a 25'
                       ELSE '26 o más'
                   END AS rango_edad,
                   p.canal_contacto, p.idPais, p.idProvincia, p.idLocalidad
            FROM Persona p
            WHERE p.deleted_at IS NULL
        ");
        DB::statement("
            CREATE OR REPLACE VIEW reporting_fact_lifecycle AS
            SELECT p.idPersona, p.idPais,
                   COALESCE(mb.algun_vigente,0) AS es_integrante_vigente,
                   COALESCE(mb.ex_integrante,0) AS fue_integrante,
                   COALESCE(pr.tiene_presencia,0) AS tiene_presencia,
                   COALESCE(su.es_suscripto,0) AS es_suscripto,
                   CASE
                       WHEN COALESCE(mb.algun_vigente,0)=1 THEN 'transformacion'
                       WHEN COALESCE(mb.ex_integrante,0)=1 THEN 'cierre'
                       WHEN COALESCE(pr.tiene_presencia,0)=1 THEN 'insercion'
                       WHEN COALESCE(su.es_suscripto,0)=1 THEN 'captado'
                       ELSE 'sin_etapa'
                   END AS etapa
            FROM Persona p
            LEFT JOIN (SELECT idPersona AS pk, MAX(vigente) AS algun_vigente,
                              MAX(CASE WHEN vigente=0 THEN 1 ELSE 0 END) AS ex_integrante
                       FROM reporting_fact_membresia GROUP BY idPersona) mb ON mb.pk = p.idPersona
            LEFT JOIN (SELECT idPersona AS pk, 1 AS tiene_presencia
                       FROM reporting_fact_participacion WHERE es_presente=1 GROUP BY idPersona) pr ON pr.pk = p.idPersona
            LEFT JOIN (SELECT idPersona AS pk, 1 AS es_suscripto
                       FROM Suscripciones WHERE idPersona IS NOT NULL GROUP BY idPersona) su ON su.pk = p.idPersona
            WHERE p.deleted_at IS NULL
        ");

        Schema::dropIfExists('reporting_person');
    }
}
