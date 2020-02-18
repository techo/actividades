UPDATE sigp.Actividad
SET limiteInscripciones = 0
WHERE
limiteInscripciones in ('', 'no hay', '11/02');

UPDATE sigp.Actividad
SET limiteInscripciones = 0
WHERE
length(limiteInscripciones) > 10;

ALTER TABLE `Actividad` 
CHANGE COLUMN `limiteInscripciones` `limiteInscripciones` INT NOT NULL DEFAULT 0 ;

ALTER TABLE `Actividad` 
CHANGE COLUMN `idUnidadOrganizacional` `idUnidadOrganizacional` INT(11) NULL ;

ALTER TABLE `Actividad` 
CHANGE COLUMN `inscripcionInterna` `inscripcionInterna` TINYINT(1) NOT NULL DEFAULT 0 ;

ALTER TABLE `Actividad` 
DROP INDEX `idUnidadOrganizacional` ;

ALTER TABLE `PuntoEncuentro` 
CHANGE COLUMN `idLocalidad` `idLocalidad` INT(11) NULL DEFAULT NULL ;

-- cambios oficinas
update atl_oficinas SET id_pais = 13 WHERE id_pais is null;

UPDATE `atl_oficinas` SET `id_pais`='172' WHERE `id`='20';
UPDATE `atl_oficinas` SET `id_pais`='172' WHERE `id`='19';
UPDATE `atl_oficinas` SET `id_pais`='172' WHERE `id`='18';
UPDATE `atl_oficinas` SET `id_pais`='172' WHERE `id`='17';
