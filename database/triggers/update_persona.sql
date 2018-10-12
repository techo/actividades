use Pilote;
DELIMITER //
DROP TRIGGER update_persona;
CREATE TRIGGER update_persona
  AFTER UPDATE
  ON Persona
  FOR EACH ROW
  BEGIN

    -- save current setting of sql_mode
    SET @old_sql_mode := @@sql_mode ;

    -- derive a new value by removing NO_ZERO_DATE and NO_ZERO_IN_DATE
    SET @new_sql_mode := @old_sql_mode ;
    SET @new_sql_mode := TRIM(BOTH ',' FROM REPLACE(CONCAT(',',@new_sql_mode,','),',NO_ZERO_DATE,'  ,','));
    SET @new_sql_mode := TRIM(BOTH ',' FROM REPLACE(CONCAT(',',@new_sql_mode,','),',NO_ZERO_IN_DATE,',','));
    SET @@sql_mode := @new_sql_mode ;

    set @pais_pilote = (SELECT nombre FROM sigp.Pais where idPais = NEW.idPais);

    SET @id_pais = (select id from sigp.atl_pais where LOWER(nombre) = LOWER(@pais_pilote));

    SET @id_sigp = (select id_sigp from sigp.auditoria where id_pilote = NEW.idPersona and tabla = 'Persona');

    SET @id_pais = 13; -- parche

    IF (@id_sigp <> 'null')
    THEN

      UPDATE sigp.Persona
      SET  `idPais` = @id_pais,
           `idPaisResidencia` = @id_pais,
           `nombres` = NEW.nombres,
           `apellidoPaterno` = NEW.apellidoPaterno,
           `apellidoMaterno` = NEW.apellidoMaterno,
           `telefono` = NEW.telefono,
           `telefonoMovil` = NEW.telefonoMovil,
           `sexo` = NEW.sexo,
           `dni` = NEW.dni,
           `recibirMails` = 1,
           `updated_at` = NOW()
      WHERE idPersona = @id_sigp;

    ELSE

      INSERT INTO sigp.replica_logs (id_pilote, tabla, log) VALUES (NEW.idPersona, 'Persona', 'UPDATE: No existe registro a actualizar en SIGP');

    END IF;

    SET @@sql_mode := @old_sql_mode;


  END;//
DELIMITER ;
