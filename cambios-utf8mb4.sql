-- save current setting of sql_mode
SET @old_sql_mode := @@sql_mode ;

-- derive a new value by removing NO_ZERO_DATE and NO_ZERO_IN_DATE
SET @new_sql_mode := @old_sql_mode ;
SET @new_sql_mode := TRIM(BOTH ',' FROM REPLACE(CONCAT(',',@new_sql_mode,','),',NO_ZERO_DATE,'  ,','));
SET @new_sql_mode := TRIM(BOTH ',' FROM REPLACE(CONCAT(',',@new_sql_mode,','),',NO_ZERO_IN_DATE,',','));
SET @@sql_mode := @new_sql_mode ;

ALTER TABLE `Actividad` MODIFY `fechaCreacion` datetime NULL DEFAULT NULL;
ALTER TABLE `Actividad` MODIFY `fechaModificacion` datetime NULL DEFAULT NULL;
ALTER TABLE `Actividad` MODIFY `fechaInicio` datetime NULL DEFAULT NULL;
ALTER TABLE `Actividad` MODIFY `fechaFin` datetime NULL DEFAULT NULL;
ALTER TABLE `Actividad` MODIFY `fechaInicioInscripciones` datetime NULL DEFAULT NULL;

UPDATE `Actividad` SET `fechaCreacion` = NULL WHERE `fechaCreacion` = '0000-00-00 00:00:00';
UPDATE `Actividad` SET `fechaModificacion` = NULL WHERE `fechaModificacion` = '0000-00-00 00:00:00';
UPDATE `Actividad` SET `fechaInicio` = NULL WHERE `fechaInicio` = '0000-00-00 00:00:00';
UPDATE `Actividad` SET `fechaFin` = NULL WHERE `fechaFin` = '0000-00-00 00:00:00';
UPDATE `Actividad` SET `fechaInicioInscripciones` = NULL WHERE `fechaInicioInscripciones` = '0000-00-00 00:00:00';

ALTER DATABASE sigp CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

ALTER TABLE Actividad CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE evaluaciones CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE evaluaciones_social CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE evaluaciones_tecnica CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE PuntoEncuentro CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE auditoria CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE Inscripcion CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE Pais CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE Persona CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE Tipo CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE UnidadOrganizacional CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

ALTER TABLE Actividad MODIFY descripcion TEXT;

ALTER TABLE Actividad
  DEFAULT CHARACTER SET utf8mb4,
  MODIFY descripcion TEXT
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
    
ALTER TABLE Actividad
  DEFAULT CHARACTER SET utf8mb4,
  MODIFY mensajeInscripcion TEXT
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
    
-- evaluaciones
ALTER TABLE evaluaciones
  DEFAULT CHARACTER SET utf8mb4,
  MODIFY texto TEXT
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
    
ALTER TABLE evaluaciones_social
  DEFAULT CHARACTER SET utf8mb4,
  MODIFY Texto TEXT
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
    
ALTER TABLE evaluaciones_tecnica
  DEFAULT CHARACTER SET utf8mb4,
  MODIFY Texto TEXT
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- PuntoEncuentro

ALTER TABLE PuntoEncuentro
  DEFAULT CHARACTER SET utf8mb4,
  MODIFY punto varchar(100)
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Tipo

ALTER TABLE Tipo
  DEFAULT CHARACTER SET utf8mb4,
  MODIFY nombre TEXT
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

ALTER TABLE Tipo
  DEFAULT CHARACTER SET utf8mb4,
  MODIFY alias TEXT
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
    
ALTER TABLE Tipo
  DEFAULT CHARACTER SET utf8mb4,
  MODIFY flujo TEXT
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
    
ALTER TABLE Tipo
  DEFAULT CHARACTER SET utf8mb4,
  MODIFY descripcion TEXT
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

ALTER TABLE Tipo
  DEFAULT CHARACTER SET utf8mb4,
  MODIFY imagen TEXT
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

SELECT column_name,character_set_name FROM information_schema.`COLUMNS` WHERE table_schema = 'sigp' and character_set_name is not null;