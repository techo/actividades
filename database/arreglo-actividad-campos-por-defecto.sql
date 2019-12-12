UPDATE Actividad
SET limiteInscripciones = 0
WHERE
limiteInscripciones in ('', 'no hay', '11/02');

UPDATE Actividad
SET limiteInscripciones = 0
WHERE
leght(limiteInscripciones) > 10;

ALTER TABLE `sigp`.`Actividad` 
CHANGE COLUMN `limiteInscripciones` `limiteInscripciones` INT NOT NULL DEFAULT 0 ;
CHANGE COLUMN `idUnidadOrganizacional` `idUnidadOrganizacional` INT(11) NULL ;
CHANGE COLUMN `inscripcionInterna` `inscripcionInterna` TINYINT(1) NOT NULL DEFAULT 0 ;