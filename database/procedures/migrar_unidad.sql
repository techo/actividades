USE sigp;
DROP PROCEDURE migrar_unidad;
DELIMITER //
CREATE PROCEDURE migrar_unidad(IN pUnidadOrganizacional INT, IN fechaCorte varchar(10))
  BEGIN

    -- Variables locales
    DECLARE done BOOLEAN DEFAULT 0;
    DECLARE idActividad INT;
     DECLARE nuevoIdActividad INT;

    -- Declaración del cursor
    DECLARE actividades_cursor CURSOR
    FOR
      SELECT a.idActividad FROM Pilote.Actividad a
      WHERE idUnidadOrganizacional = pUnidadOrganizacional
        AND fechaCreacion >= fechaCorte;

    -- Declaración del manejador de continuidad
    DECLARE CONTINUE HANDLER FOR SQLSTATE '02000' SET done=1;

    -- Abre el cursor
    OPEN actividades_cursor;

    -- Loop de todos los registros
    actividades_loop: LOOP
      -- Obtener id de Actividad
      FETCH actividades_cursor INTO idActividad;

      IF done THEN
        LEAVE actividades_loop;
      END IF;

      CALL sigp.inserta_actividad(idActividad, nuevoIdActividad);
      CALL inserta_puntos_encuentro(idActividad, nuevoIdActividad);
      CALL sigp.inserta_inscripciones(idActividad, nuevoIdActividad);

      -- fin loop
    END LOOP;
    -- Cierra el cursor
    CLOSE actividades_cursor;


  END //
DELIMITER ;
