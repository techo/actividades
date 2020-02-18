ALTER TABLE `sigp`.`Tipo` 
DROP COLUMN `flujo`,
DROP COLUMN `alias`,
DROP COLUMN `fyv`,
DROP COLUMN `hs`;

DELETE FROM `sigp`.`migrations` WHERE `id`='63';