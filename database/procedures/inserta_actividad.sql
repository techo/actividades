USE sigp;
DROP PROCEDURE inserta_actividad;
DELIMITER //
CREATE PROCEDURE inserta_actividad (IN pIdActividad INT, OUT idActividadNuevo INT)
  BEGIN

    -- save current setting of sql_mode
    SET @old_sql_mode := @@sql_mode ;

    -- derive a new value by removing NO_ZERO_DATE and NO_ZERO_IN_DATE
    SET @new_sql_mode := @old_sql_mode ;
    SET @new_sql_mode := TRIM(BOTH ',' FROM REPLACE(CONCAT(',',@new_sql_mode,','),',NO_ZERO_DATE,'  ,','));
    SET @new_sql_mode := TRIM(BOTH ',' FROM REPLACE(CONCAT(',',@new_sql_mode,','),',NO_ZERO_IN_DATE,',','));
    SET @@sql_mode := @new_sql_mode ;

    SET @id_pais = (select id from sigp.atl_pais where nombre = 'Argentina');


    INSERT INTO sigp.Actividad (
        `idTipo`,
        `fechaCreacion`,
        `fechaModificacion`,
        `fechaInicio`,
        `fechaFin`,
        `fechaInicioFinFormato`,
        `fechaInicioInscripciones`,
        `fechaFinInscripciones`,
        `limiteInscripciones`,
        `idUnidadOrganizacional`,
        `idOficina`,
        `nombreActividad`,
        `descripcion`,
        `lugar`,
        `casasPlanificadas`,
        `casasConstruidas`,
        `comentarios`,
        `tipoConstruccion`,
        `estadoConstruccion`,
        `idPrograma`,
        `mensajeInscripcion`,
        `idEncuestaDinamica`,
        `numConstruccion`,
        `pApMat`,
        `pDNI`,
        `pFonoMovil`,
        `pUniversidad`,
        `pCarrera`,
        `pAnoEstudio`,
        `pAcompanante`,
        `tPortugues`,
        `enviarMail`,
        `actividadSecundaria`,
        `compromiso`,
        `idListaCTCT`,
        `mostrarFB`,
        `presupuesto`,
        `inscripcion`,
        `inscripcionInterna`,
        `idPersonaCreacion`,
        `idPersonaModificacion`,
        `idEmpresa`,
        `costo`,
        `moneda`,
        `estadoDefault`,
        `puntosEnvio`,
        `captaciones`,
        `pAcompanantePost`,
        `mailBeca`,
        `idFormulario`,
        `fechaInicioEvaluaciones`,
        `fechaFinEvaluaciones`,
        `idAsentamiento`,
        `idZona`,
        `linkFormularioEvaluacion`,
        `statusMailSeguimiento`,
        `mailSeguimiento`,
        `destacada`,
        `EnviarMailPago`,
        `MailPago`,
        `CostoConMoneda`,
        `LinkPago`,
        `PuntosEnvioUL`,
        `CaptacionesUL`,
        `idPais`,
        `idProvincia`,
        `idLocalidad`
        )
    SELECT
       idTipo,
       CASE WHEN fechaCreacion = '0000-00-00 00:00:00' THEN NULL ELSE fechaCreacion END,
       CASE WHEN fechaModificacion = '0000-00-00 00:00:00' THEN NULL ELSE fechaModificacion END,
       CASE WHEN fechaInicio = '0000-00-00 00:00:00' THEN NULL ELSE fechaInicio END,
       CASE WHEN fechaFin = '0000-00-00 00:00:00' THEN NULL ELSE fechaFin END,
       CASE WHEN fechaInicioFinFormato = '0000-00-00 00:00:00' THEN NULL ELSE fechaInicioFinFormato END,
       CASE WHEN fechaInicioInscripciones = '0000-00-00 00:00:00' THEN NULL ELSE fechaInicioInscripciones END,
       CASE WHEN fechaFinInscripciones = '0000-00-00 00:00:00' THEN NULL ELSE fechaFinInscripciones END,
       limiteInscripciones,
       idUnidadOrganizacional,
       NULL,
       nombreActividad,
       descripcion,
       lugar,
       casasPlanificadas,
       casasConstruidas,
       comentarios,
       tipoConstruccion,
       'Cerrada', -- las actividades de Pilote se replican con estado cerrada
       idPrograma,
       mensajeInscripcion,
       idEncuestaDinamica,
       numConstruccion,
       pApMat,
       pDNI,
       pFonoMovil,
       pUniversidad,
       pCarrera,
       pAnoEstudio,
       pAcompanante,
       tPortugues,
       enviarMail,
       actividadSecundaria,
       compromiso,
       idListaCTCT,
       mostrarFB,
       presupuesto,
       inscripcion,
       inscripcionInterna,
       CASE WHEN idPersonaModificacion = (SELECT id_pilote FROM sigp.auditoria WHERE id_pilote = act.idPersonaModificacion and tabla = 'Persona') THEN (SELECT id_sigp FROM sigp.auditoria WHERE id_pilote = act.idPersonaModificacion and tabla = 'Persona') ELSE idPersonaModificacion END, -- Si el id de la persona se encuentra en la tabla de auditoria, quiere decir que en SIGP tiene otro ID, y es el que debe ir, sino, mantiene el mismo  ID (corresponde a idPersonaCreacion)
       CASE WHEN idPersonaModificacion = (SELECT id_pilote FROM sigp.auditoria WHERE id_pilote = act.idPersonaModificacion and tabla = 'Persona') THEN (SELECT id_sigp FROM sigp.auditoria WHERE id_pilote = act.idPersonaModificacion and tabla = 'Persona') ELSE idPersonaModificacion END, -- Si el id de la persona se encuentra en la tabla de auditoria, quiere decir que en SIGP tiene otro ID, y es el que debe ir, sino, mantiene el mismo  ID
       idEmpresa,
       costo,
       moneda,
       estadoDefault,
       puntosEnvio,
       captaciones,
       pAcompanantePost,
       mailBeca,
       idFormulario,
       CASE WHEN fechaInicioEvaluaciones = '0000-00-00 00:00:00' THEN NULL ELSE fechaInicioEvaluaciones END,
       CASE WHEN fechaFinEvaluaciones = '0000-00-00 00:00:00' THEN NULL ELSE fechaFinEvaluaciones END,
       idAsentamiento,
       idZona,
       linkFormularioEvaluacion,
       statusMailSeguimiento,
       mailSeguimiento,
       destacada,
       EnviarMailPago,
       MailPago,
       CostoConMoneda,
       LinkPago,
       PuntosEnvioUL,
       CaptacionesUL,
       @id_pais,
       1, -- provincia buenos aires
       2383 -- localidad sin especificar
    FROM Pilote.Actividad act
    WHERE idActividad = pIdActividad;

    SELECT LAST_INSERT_ID() into idActividadNuevo;

    SET @@sql_mode := @old_sql_mode;

  END//
DELIMITER ;
