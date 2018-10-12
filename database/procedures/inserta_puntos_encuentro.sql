USE sigp;
DROP PROCEDURE inserta_puntos_encuentro;
DELIMITER //
CREATE PROCEDURE inserta_puntos_encuentro(IN idActividadViejo INT, IN idActividadNuevo INT)
  BEGIN

    DECLARE done BOOLEAN DEFAULT 0;
    DECLARE punto INT;
    DECLARE puntoNuevo INT;
    DECLARE id_pais INT;

    -- Declaración del cursor
    DECLARE puntos_cursor CURSOR
    FOR
      SELECT p.idPuntoEncuentro FROM Pilote.PuntoEncuentro p
      WHERE p.idActividad = idActividadViejo;

    -- Declaración del manejador de continuidad
    DECLARE CONTINUE HANDLER FOR SQLSTATE '02000' SET done=1;

    select id INTO id_pais from sigp.atl_pais where nombre = 'Argentina';

    OPEN puntos_cursor;

    puntos_loop: LOOP

      FETCH puntos_cursor INTO punto;

      IF done THEN
        LEAVE puntos_loop;
      END IF;


      INSERT INTO sigp.PuntoEncuentro
      (
        idZona,
        punto,
        horario,
        idPersona,
        idActividad,
        idPais,
        idProvincia,
        idLocalidad
      )
      SELECT
       NULL, -- Zona no se usa en SIGP
       punto,
       horario,
       CASE WHEN idPersona = (SELECT id_pilote FROM sigp.auditoria WHERE id_pilote = p.idPersona and tabla = 'Persona') THEN (SELECT id_sigp FROM sigp.auditoria WHERE id_pilote = p.idPersona and tabla = 'Persona') ELSE idPersona END, -- Si el id de la persona se encuentra en la tabla de auditoria, quiere decir que en SIGP tiene otro ID, y es el que debe ir, sino, mantiene el mismo  ID,
       idActividadNuevo, -- id de nueva actividad
       id_pais, -- Argentina
       0, -- Colocar idProvincia por parámetro
       0 -- Colocar idLocalidad por parámetro
      FROM Pilote.PuntoEncuentro p
      WHERE p.idPuntoEncuentro = punto;

      SELECT LAST_INSERT_ID() into puntoNuevo;

      INSERT INTO sigp.auditoria (id_pilote, id_sigp, tabla) VALUES (punto, puntoNuevo, 'PuntoEncuentro');

  END LOOP;

  END //

DELIMITER ;
