USE sigp;
DROP PROCEDURE inserta_inscripciones;
DELIMITER //
CREATE PROCEDURE inserta_inscripciones(IN idActividadViejo INT, IN idActividadNuevo INT)
  BEGIN

    DECLARE done BOOLEAN DEFAULT 0;
    DECLARE idInscripcion INT;
    DECLARE idInscripcionNueva INT;

    -- Declaración del cursor
    DECLARE inscripciones_cursor CURSOR
    FOR
      SELECT a.idInscripcion FROM Pilote.Inscripcion a
      WHERE a.idActividad = idActividadViejo;

    -- Declaración del manejador de continuidad
    DECLARE CONTINUE HANDLER FOR SQLSTATE '02000' SET done=1;

    OPEN inscripciones_cursor;

    inscripciones_loop: LOOP

      FETCH inscripciones_cursor INTO idInscripcion;

      IF done THEN
        LEAVE inscripciones_loop;
      END IF;

      CALL inserta_inscripcion(idInscripcion, idActividadNuevo, idInscripcionNueva);

    END LOOP;

    CLOSE inscripciones_cursor;


  END //
DELIMITER ;
