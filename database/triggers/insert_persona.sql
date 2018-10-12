use Pilote;
DELIMITER //
DROP TRIGGER insert_persona;
CREATE TRIGGER insert_persona
  AFTER INSERT 
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

    SET @count = (select count(*) from sigp.Persona where mail = NEW.mail);

    SET @fechaNacimiento = NEW.fechaNacimiento;

    IF @fechaNacimiento = '0000-00-00 00:00:00' THEN
      SET @fechaNacimiento = '1900-01-01';
    END IF;

    -- IF (@id_pais = NULL OR @id_pais = 'NULL' OR @id_pais = '' OR @id_pais = 0) THEN
    SET @id_pais = 13;
    -- END IF;


    IF (@count = 0)
    THEN

      INSERT INTO sigp.Persona (`idPais`,
                                `idProvincia`,
                                `idLocalidad`,
                                `idPaisResidencia`,
                                `idCiudad`,
                                `idUnidadOrganizacional`,
                                `nombres`,
                                `apellidoPaterno`,
                                `apellidoMaterno`,
                                `fechaNacimiento`,
                                `telefono`,
                                `telefonoMovil`,
                                `sexo`,
                                `dni`,
                                `mail`,
                                `password`,
                                `idUniversidad`,
                                `idColegio`,
                                `universidad_string`,
                                `carrera`,
                                `anoEstudio`,
                                `loginActiveDirectory`,
                                `loginMailUTPMP`,
                                `nuevaPortada`,
                                `idContactoCTCT`,
                                `statusCTCT`,
                                `lenguaje`,
                                `captacion`,
                                `configuracion`,
                                `idEmpresa`,
                                `terminosVoluntarioPermanente`,
                                `terminosVoluntarioMasivo`,
                                `idRegionLT`,
                                `calle`,
                                `numero`,
                                `piso`,
                                `dpto`,
                                `fechaInscripcion`,
                                `ultimaEntrada`,
                                `ultimaActualizacion`,
                                `dispHoraria`,
                                `recibirMails`,
                                `idCarrera`,
                                `idAreaEstudio`,
                                `Vecino_Voluntario`,
                                `Barrio_Vecino`,
                                `remember_token`,
                                `created_at`,
                                `updated_at`,
                                `verificado`,
                                `google_id`,
                                `facebook_id`,
                                `unsubscribe_token`,
                                `acepta_marketing`)
      VALUES (@id_pais,
              NULL,
              NULL,
              @id_pais,
              0,
              0,
              NEW.nombres,
              NEW.apellidoPaterno,
              NEW.apellidoMaterno,
              @fechaNacimiento,
              NEW.telefono,
              NEW.telefonoMovil,
              NEW.sexo,
              NEW.dni,
              NEW.mail,
              NEW.password,
              NEW.idUniversidad,
              NEW.idColegio,
              NEW.universidad_string,
              '',
              '',
              NEW.loginActiveDirectory,
              NEW.loginMailUTPMP,
              NEW.nuevaPortada,
              '',
              '',
              '',
              NEW.captacion,
              NEW.configuracion,
              NEW.idEmpresa,
              NEW.terminosVoluntarioPermanente,
              NEW.terminosVoluntarioMasivo,
              0,
              NEW.calle,
              NEW.numero,
              NEW.piso,
              NEW.dpto,
              NEW.fechaInscripcion,
              NEW.ultimaEntrada,
              NEW.ultimaActualizacion,
              NEW.dispHoraria,
              1,
              NEW.idCarrera,
              NEW.idAreaEstudio,
              NEW.Vecino_Voluntario,
              NEW.Barrio_Vecino,
              NULL,
              NOW(),
              NOW(),
              0,
              NULL,
              NULL,
              UUID(),
              1);

      SET @idPersona = (SELECT LAST_INSERT_ID());

      INSERT INTO sigp.auditoria (id_pilote, id_sigp, tabla) VALUES (NEW.idPersona, @idPersona, 'Persona');

    ELSE

      INSERT INTO sigp.replica_logs (id_pilote, tabla, log) VALUES (NEW.idPersona, 'Persona', 'El mail ya existe en SIGP');

    END IF;

    SET @@sql_mode := @old_sql_mode;

  END;//
DELIMITER ;
