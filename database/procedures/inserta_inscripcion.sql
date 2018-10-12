USE sigp;
DROP PROCEDURE inserta_inscripcion;
DELIMITER //
CREATE PROCEDURE inserta_inscripcion(IN idInscripcionViejo INT, IN idActividadNuevo INT, OUT idInscripcionNueva INT)
  BEGIN

    -- save current setting of sql_mode
    SET @old_sql_mode := @@sql_mode ;

    -- derive a new value by removing NO_ZERO_DATE and NO_ZERO_IN_DATE
    SET @new_sql_mode := @old_sql_mode ;
    SET @new_sql_mode := TRIM(BOTH ',' FROM REPLACE(CONCAT(',',@new_sql_mode,','),',NO_ZERO_DATE,'  ,','));
    SET @new_sql_mode := TRIM(BOTH ',' FROM REPLACE(CONCAT(',',@new_sql_mode,','),',NO_ZERO_IN_DATE,',','));
    SET @@sql_mode := @new_sql_mode ;

    INSERT INTO sigp.Inscripcion
    (
      idActividad,
      idPersona,
      idUnidadOrganizacional,
      idEscuelaRol,
      idCuadrillaRol,
      idActividadRol,
      idMesaTrabajoRol,
      idProgramaRol,
      idMesaTrabajoLTRol,
      idLocalidadRol,
      rol,
      fechaInscripcion,
      fechaFin,
      estado,
      evaluacion,
      aceptarCompromiso,
      acompanante,
      comentarios,
      fechaCreacion,
      fechaModificacion,
      idPersonaModificacion,
      montoPago,
      moneda,
      subsidio,
      idRazonSubsidio,
      presente,
      puntoEnvio,
      captacion,
      pago,
      fechaPago,
      fechaSubsidio,
      idPuntoEncuentro,
      idAreadeInteres,
      created_at,
      updated_at
    )
    SELECT
     idActividadNuevo, -- nuevo id de Actividad
     CASE WHEN idPersona = (SELECT id_pilote FROM sigp.auditoria WHERE id_pilote = i.idPersona and tabla = 'Persona') THEN (SELECT id_sigp FROM sigp.auditoria WHERE id_pilote = i.idPersona and tabla = 'Persona') ELSE idPersona END, -- Si el id de la persona se encuentra en la tabla de auditoria, quiere decir que en SIGP tiene otro ID, y es el que debe ir, sino, mantiene el mismo  ID
     idUnidadOrganizacional,
     NULL,
     NULL,
     NULL,
     NULL,
     NULL,
     NULL,
     NULL,
     rol,
     CASE WHEN fechaInscripcion = '0000-00-00 00:00:00' THEN NULL ELSE fechaInscripcion END,
     CASE WHEN fechaFin = '0000-00-00 00:00:00' THEN NULL ELSE fechaFin END,
     estado,
     evaluacion,
     aceptarCompromiso,
     acompanante,
     comentarios,
     CASE WHEN fechaCreacion = '0000-00-00 00:00:00' THEN NULL ELSE fechaCreacion END,
     CASE WHEN fechaModificacion = '0000-00-00 00:00:00' THEN NULL ELSE fechaModificacion END,
     CASE WHEN idPersonaModificacion = (SELECT id_pilote FROM sigp.auditoria WHERE id_pilote = i.idPersonaModificacion and tabla = 'Persona') THEN (SELECT id_sigp FROM sigp.auditoria WHERE id_pilote = i.idPersonaModificacion and tabla = 'Persona') ELSE idPersonaModificacion END, -- Si el id de la persona se encuentra en la tabla de auditoria, quiere decir que en SIGP tiene otro ID, y es el que debe ir, sino, mantiene el mismo  ID
     montoPago,
     moneda,
     subsidio,
     idRazonSubsidio,
     presente,
     puntoEnvio,
     captacion,
     pago,
     CASE WHEN fechaPago = '0000-00-00 00:00:00' THEN NULL ELSE fechaPago END,
     CASE WHEN fechaSubsidio = '0000-00-00 00:00:00' THEN NULL ELSE fechaSubsidio END,
     CASE WHEN idPuntoEncuentro = (SELECT id_pilote FROM sigp.auditoria WHERE id_pilote = i.idPuntoEncuentro and tabla = 'PuntoEncuentro') THEN (SELECT id_sigp FROM sigp.auditoria WHERE id_pilote = i.idPuntoEncuentro and tabla = 'PuntoEncuentro') ELSE idPuntoEncuentro END
     idAreadeInteres,
     NOW(),
     NOW()
    FROM Pilote.Inscripcion i
    WHERE i.idInscripcion = idInscripcionViejo;

    SELECT LAST_INSERT_ID() into idInscripcionNueva;

    SET @@sql_mode := @old_sql_mode;

  END //
DELIMITER ;
