# ************************************************************
# Sequel Pro SQL dump
# Versión 4541
#
# http://www.sequelpro.com/
# https://github.com/sequelpro/sequelpro
#
# Host: 192.168.10.10 (MySQL 5.7.21-0ubuntu0.16.04.1)
# Base de datos: atlas_vanilla
# Tiempo de Generación: 2018-07-30 19:06:32 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Volcado de tabla Actividad
# ------------------------------------------------------------

DROP TABLE IF EXISTS `Actividad`;

CREATE TABLE `Actividad` (
  `idActividad` int(11) NOT NULL AUTO_INCREMENT,
  `idTipo` int(11) NOT NULL,
  `fechaCreacion` datetime NOT NULL,
  `fechaModificacion` datetime NOT NULL,
  `fechaInicio` datetime NOT NULL,
  `fechaFin` datetime NOT NULL,
  `fechaInicioFinFormato` varchar(20) COLLATE utf8_spanish_ci DEFAULT 'd/m/Y',
  `fechaInicioInscripciones` datetime DEFAULT NULL,
  `fechaFinInscripciones` datetime DEFAULT NULL,
  `limiteInscripciones` varchar(20) COLLATE utf8_spanish_ci NOT NULL,
  `idUnidadOrganizacional` int(11) NOT NULL,
  `nombreActividad` varchar(300) COLLATE utf8_spanish_ci NOT NULL,
  `descripcion` varchar(3000) COLLATE utf8_spanish_ci DEFAULT NULL,
  `lugar` varchar(300) COLLATE utf8_spanish_ci NOT NULL,
  `casasPlanificadas` varchar(20) COLLATE utf8_spanish_ci NOT NULL,
  `casasConstruidas` varchar(20) COLLATE utf8_spanish_ci NOT NULL,
  `comentarios` text COLLATE utf8_spanish_ci NOT NULL,
  `tipoConstruccion` varchar(300) COLLATE utf8_spanish_ci NOT NULL,
  `estadoConstruccion` varchar(200) COLLATE utf8_spanish_ci NOT NULL DEFAULT 'Abierta',
  `idPrograma` int(11) DEFAULT NULL,
  `mensajeInscripcion` text COLLATE utf8_spanish_ci NOT NULL,
  `idEncuestaDinamica` int(11) NOT NULL,
  `numConstruccion` int(11) DEFAULT NULL,
  `pApMat` tinyint(1) NOT NULL DEFAULT '1',
  `pDNI` tinyint(1) NOT NULL DEFAULT '1',
  `pFonoMovil` tinyint(1) NOT NULL DEFAULT '1',
  `pUniversidad` tinyint(1) NOT NULL DEFAULT '1',
  `pCarrera` tinyint(1) NOT NULL DEFAULT '1',
  `pAnoEstudio` tinyint(1) NOT NULL DEFAULT '1',
  `pAcompanante` tinyint(1) NOT NULL DEFAULT '0',
  `tPortugues` tinyint(1) NOT NULL DEFAULT '0',
  `enviarMail` tinyint(1) NOT NULL DEFAULT '1',
  `actividadSecundaria` tinyint(1) NOT NULL,
  `compromiso` tinyint(1) NOT NULL DEFAULT '1',
  `idListaCTCT` varchar(300) COLLATE utf8_spanish_ci NOT NULL,
  `mostrarFB` tinyint(1) NOT NULL,
  `presupuesto` varchar(300) COLLATE utf8_spanish_ci DEFAULT NULL,
  `inscripcion` varchar(300) COLLATE utf8_spanish_ci DEFAULT NULL,
  `inscripcionInterna` tinyint(1) NOT NULL,
  `idPersonaModificacion` int(11) DEFAULT NULL,
  `idEmpresa` int(11) DEFAULT NULL,
  `costo` decimal(10,0) DEFAULT '0',
  `moneda` varchar(10) COLLATE utf8_spanish_ci DEFAULT NULL,
  `estadoDefault` varchar(100) COLLATE utf8_spanish_ci DEFAULT NULL,
  `puntosEnvio` text COLLATE utf8_spanish_ci,
  `captaciones` text COLLATE utf8_spanish_ci,
  `pAcompanantePost` tinyint(1) DEFAULT NULL,
  `mailBeca` text COLLATE utf8_spanish_ci,
  `idFormulario` int(11) DEFAULT NULL,
  `fechaInicioEvaluaciones` datetime DEFAULT NULL,
  `fechaFinEvaluaciones` datetime DEFAULT NULL,
  `idAsentamiento` int(11) DEFAULT NULL,
  `idZona` int(11) DEFAULT NULL,
  `linkFormularioEvaluacion` varchar(300) COLLATE utf8_spanish_ci DEFAULT NULL,
  `statusMailSeguimiento` int(11) DEFAULT NULL,
  `mailSeguimiento` text COLLATE utf8_spanish_ci,
  `destacada` tinyint(4) DEFAULT NULL,
  `EnviarMailPago` tinyint(4) DEFAULT NULL,
  `MailPago` text COLLATE utf8_spanish_ci,
  `CostoConMoneda` varchar(5) COLLATE utf8_spanish_ci DEFAULT NULL,
  `LinkPago` varchar(1000) COLLATE utf8_spanish_ci DEFAULT NULL,
  `PuntosEnvioUL` text COLLATE utf8_spanish_ci,
  `CaptacionesUL` text COLLATE utf8_spanish_ci,
  PRIMARY KEY (`idActividad`),
  KEY `idTipo` (`idTipo`),
  KEY `idUnidadOrganizacional` (`idUnidadOrganizacional`),
  KEY `idPrograma` (`idPrograma`),
  KEY `fk_Actividad_1_idx` (`idPersonaModificacion`),
  KEY `Actividad_ibfk_7` (`idEmpresa`),
  KEY `Actividad_ibfk_8_idx` (`idFormulario`),
  KEY `Actividad_ibfk_9_idx` (`idAsentamiento`),
  KEY `fk_Actividad_1_idx1` (`idZona`),
  CONSTRAINT `Actividad_ibfk_3` FOREIGN KEY (`idTipo`) REFERENCES `Tipo` (`idTipo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;



# Volcado de tabla Inscripcion
# ------------------------------------------------------------

DROP TABLE IF EXISTS `Inscripcion`;

CREATE TABLE `Inscripcion` (
  `idInscripcion` int(11) NOT NULL AUTO_INCREMENT,
  `idActividad` int(11) DEFAULT NULL,
  `idPersona` int(11) NOT NULL,
  `idUnidadOrganizacional` int(11) DEFAULT NULL,
  `idEscuelaRol` int(11) DEFAULT NULL,
  `idCuadrillaRol` int(11) DEFAULT NULL,
  `idActividadRol` int(11) DEFAULT NULL,
  `idMesaTrabajoRol` int(11) DEFAULT NULL,
  `idProgramaRol` int(11) DEFAULT NULL,
  `idMesaTrabajoLTRol` int(11) DEFAULT NULL,
  `idLocalidadRol` int(11) DEFAULT NULL,
  `rol` varchar(300) COLLATE utf8_spanish_ci DEFAULT NULL,
  `fechaInscripcion` datetime DEFAULT NULL,
  `fechaFin` datetime DEFAULT NULL,
  `estado` varchar(200) COLLATE utf8_spanish_ci NOT NULL,
  `evaluacion` tinyint(1) NOT NULL,
  `aceptarCompromiso` tinyint(1) NOT NULL DEFAULT '0',
  `acompanante` varchar(200) COLLATE utf8_spanish_ci NOT NULL,
  `comentarios` text COLLATE utf8_spanish_ci,
  `fechaCreacion` datetime DEFAULT NULL,
  `fechaModificacion` datetime DEFAULT NULL,
  `idPersonaModificacion` int(11) DEFAULT NULL,
  `montoPago` decimal(10,0) DEFAULT NULL,
  `moneda` varchar(10) COLLATE utf8_spanish_ci DEFAULT NULL,
  `subsidio` decimal(10,0) DEFAULT NULL,
  `idRazonSubsidio` int(11) DEFAULT NULL,
  `presente` int(11) DEFAULT NULL,
  `puntoEnvio` varchar(200) COLLATE utf8_spanish_ci DEFAULT NULL,
  `captacion` varchar(200) COLLATE utf8_spanish_ci DEFAULT NULL,
  `pago` int(11) DEFAULT NULL,
  `fechaPago` datetime DEFAULT NULL,
  `fechaSubsidio` varchar(45) COLLATE utf8_spanish_ci DEFAULT NULL,
  `idPuntoEncuentro` int(11) DEFAULT NULL,
  `idAreadeInteres` int(11) DEFAULT NULL,
  PRIMARY KEY (`idInscripcion`),
  KEY `idActividad` (`idActividad`),
  KEY `idPersona` (`idPersona`),
  KEY `idActividadRol` (`idActividadRol`),
  KEY `idEscuelaRol` (`idEscuelaRol`),
  KEY `idCuadrillaRol` (`idCuadrillaRol`),
  KEY `idUnidadOrganizacional` (`idUnidadOrganizacional`),
  KEY `idProgramaRol` (`idProgramaRol`),
  KEY `idMesaTrabajoLTRol` (`idMesaTrabajoLTRol`),
  KEY `idMesaTrabajoRol` (`idMesaTrabajoRol`),
  KEY `idLocalidadRol` (`idLocalidadRol`),
  KEY `fk_Inscripcion_1_idx` (`idPersonaModificacion`),
  KEY `Inscripcion_ibfk_45_idx` (`idRazonSubsidio`),
  KEY `Inscripcion_ibfk_46_idx` (`idPuntoEncuentro`),
  KEY `fk_Inscripcion_1_idx1` (`idAreadeInteres`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;



# Volcado de tabla migrations
# ------------------------------------------------------------

DROP TABLE IF EXISTS `migrations`;

CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Volcado de tabla Persona
# ------------------------------------------------------------

DROP TABLE IF EXISTS `Persona`;

CREATE TABLE `Persona` (
  `idPersona` int(11) NOT NULL AUTO_INCREMENT,
  `idPais` int(11) NOT NULL,
  `idPaisResidencia` int(11) NOT NULL,
  `idCiudad` int(11) NOT NULL,
  `idUnidadOrganizacional` int(11) NOT NULL,
  `nombres` varchar(300) COLLATE utf8_spanish_ci NOT NULL,
  `apellidoPaterno` varchar(300) COLLATE utf8_spanish_ci NOT NULL,
  `apellidoMaterno` varchar(300) COLLATE utf8_spanish_ci DEFAULT NULL,
  `fechaNacimiento` datetime NOT NULL,
  `telefono` varchar(300) COLLATE utf8_spanish_ci DEFAULT NULL,
  `telefonoMovil` varchar(300) COLLATE utf8_spanish_ci NOT NULL,
  `sexo` varchar(1) COLLATE utf8_spanish_ci DEFAULT NULL,
  `dni` varchar(50) COLLATE utf8_spanish_ci NOT NULL,
  `mail` varchar(300) COLLATE utf8_spanish_ci NOT NULL,
  `password` varchar(400) COLLATE utf8_spanish_ci NOT NULL,
  `idUniversidad` int(11) DEFAULT NULL,
  `idColegio` int(11) DEFAULT NULL,
  `universidad_string` varchar(300) COLLATE utf8_spanish_ci DEFAULT NULL,
  `carrera` varchar(300) COLLATE utf8_spanish_ci NOT NULL,
  `anoEstudio` varchar(20) COLLATE utf8_spanish_ci NOT NULL,
  `loginActiveDirectory` varchar(100) COLLATE utf8_spanish_ci DEFAULT NULL,
  `loginMailUTPMP` varchar(200) COLLATE utf8_spanish_ci DEFAULT NULL,
  `nuevaPortada` tinyint(4) NOT NULL DEFAULT '0',
  `idContactoCTCT` varchar(300) COLLATE utf8_spanish_ci NOT NULL,
  `statusCTCT` varchar(30) COLLATE utf8_spanish_ci NOT NULL,
  `lenguaje` varchar(300) COLLATE utf8_spanish_ci NOT NULL,
  `captacion` varchar(500) COLLATE utf8_spanish_ci DEFAULT NULL,
  `configuracion` blob,
  `idEmpresa` int(11) DEFAULT NULL,
  `terminosVoluntarioPermanente` tinyint(4) DEFAULT '0',
  `terminosVoluntarioMasivo` tinyint(4) DEFAULT '0',
  `idRegionLT` int(11) NOT NULL,
  `calle` varchar(255) COLLATE utf8_spanish_ci DEFAULT NULL,
  `numero` varchar(255) COLLATE utf8_spanish_ci DEFAULT NULL,
  `piso` varchar(255) COLLATE utf8_spanish_ci DEFAULT NULL,
  `dpto` varchar(255) COLLATE utf8_spanish_ci DEFAULT NULL,
  `fechaInscripcion` datetime DEFAULT NULL,
  `ultimaEntrada` datetime DEFAULT NULL,
  `ultimaActualizacion` datetime DEFAULT NULL,
  `dispHoraria` varchar(400) COLLATE utf8_spanish_ci DEFAULT NULL,
  `recibirMails` int(11) DEFAULT NULL,
  `idCarrera` int(11) DEFAULT NULL,
  `idAreaEstudio` int(11) DEFAULT NULL,
  `Vecino_Voluntario` char(1) COLLATE utf8_spanish_ci DEFAULT NULL,
  `Barrio_Vecino` varchar(40) COLLATE utf8_spanish_ci DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8_spanish_ci DEFAULT NULL,
  PRIMARY KEY (`idPersona`),
  KEY `idPais` (`idPais`),
  KEY `idPaisResidencia` (`idPaisResidencia`),
  KEY `idCiudad` (`idCiudad`),
  KEY `mail` (`mail`(255)),
  KEY `Persona_ibfk_4` (`idEmpresa`),
  KEY `fk_Persona_Universidad` (`idUniversidad`),
  KEY `fk_Persona_Colegio` (`idColegio`),
  KEY `fk_Persona_RegionLT_idx` (`idRegionLT`),
  KEY `idx_Persona_1` (`apellidoPaterno`(255),`apellidoMaterno`(255),`nombres`(255)),
  KEY `fk_Persona_Carrera_idx` (`idCarrera`),
  KEY `ix_Persona_DniPais` (`idPaisResidencia`,`dni`),
  KEY `Persona_ibfk_5_idx` (`idAreaEstudio`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;



# Volcado de tabla PuntoEncuentro
# ------------------------------------------------------------

DROP TABLE IF EXISTS `PuntoEncuentro`;

CREATE TABLE `PuntoEncuentro` (
  `idPuntoEncuentro` int(11) NOT NULL AUTO_INCREMENT,
  `idZona` int(11) DEFAULT NULL,
  `punto` varchar(100) DEFAULT NULL,
  `horario` time DEFAULT NULL,
  `idPersona` int(11) DEFAULT NULL,
  `idActividad` int(11) DEFAULT NULL,
  PRIMARY KEY (`idPuntoEncuentro`),
  KEY `fk_PuntoEncuentro_1_idx` (`idZona`),
  KEY `fk_PuntoEncuentro_2_idx` (`idPersona`),
  KEY `fk_PuntoEncuentro_3_idx` (`idActividad`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Volcado de tabla Tipo
# ------------------------------------------------------------

DROP TABLE IF EXISTS `Tipo`;

CREATE TABLE `Tipo` (
  `idTipo` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(300) COLLATE utf8_spanish_ci NOT NULL,
  `hs` tinyint(1) NOT NULL,
  `fyv` tinyint(1) NOT NULL,
  `alias` varchar(10) COLLATE utf8_spanish_ci DEFAULT NULL,
  PRIMARY KEY (`idTipo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

LOCK TABLES `Tipo` WRITE;
/*!40000 ALTER TABLE `Tipo` DISABLE KEYS */;

INSERT INTO `Tipo` (`idTipo`, `nombre`, `hs`, `fyv`, `alias`)
VALUES
	(1,'Plan Construcción',0,0,'cc'),
	(2,'Plan Educacion',1,0,NULL),
	(3,'Plan Salud',1,0,NULL),
	(4,'Plan Microcreditos',1,0,NULL),
	(5,'Plan Juridico',1,0,NULL),
	(6,'Plan Fontecho',1,0,NULL),
	(7,'Plan Talleres electivos',1,0,NULL),
	(8,'Plan Barrios',0,0,NULL),
	(9,'Plan Equipo Construcciones',0,0,NULL),
	(10,'Plan de Ahorro',1,0,NULL),
	(11,'Detección y Asignación',0,1,'dya'),
	(12,'Coordinador',1,0,NULL),
	(13,'Secundarios',0,0,NULL),
	(14,'Actividad Masiva',0,1,'masiva'),
	(15,'Plan Urbano',1,0,NULL),
	(16,'Colecta',0,1,'colecta'),
	(17,'La noche sin techo',0,1,'lnst'),
	(18,'Campaña universitaria',0,1,'cuni'),
	(19,'Plan Ambiental',1,0,NULL),
	(20,'Habilitación Social',0,1,'hs'),
	(21,'Charla Informativa',0,1,'charla-inf'),
	(22,'Juegoteca',1,0,'jue'),
	(23,'Emprendedores',1,0,'emp'),
	(24,'Oficios',1,0,'ofi'),
	(25,'Apoyo Escolar',1,0,'apo'),
	(26,'Arte',1,0,'art');

/*!40000 ALTER TABLE `Tipo` ENABLE KEYS */;
UNLOCK TABLES;


# Volcado de tabla UnidadOrganizacional
# ------------------------------------------------------------

DROP TABLE IF EXISTS `UnidadOrganizacional`;

CREATE TABLE `UnidadOrganizacional` (
  `idUnidadOrganizacional` int(11) NOT NULL AUTO_INCREMENT,
  `idUnidadPadre` int(11) DEFAULT NULL,
  `nombre` varchar(300) COLLATE utf8_spanish_ci NOT NULL,
  `idPais` int(11) DEFAULT NULL,
  `heredarPermisos` tinyint(1) NOT NULL,
  `idEquipo` int(11) DEFAULT NULL,
  `claveApiActiveCampaign` varchar(255) COLLATE utf8_spanish_ci NOT NULL,
  `direccion` varchar(255) COLLATE utf8_spanish_ci NOT NULL,
  `idCiudad` int(11) NOT NULL,
  PRIMARY KEY (`idUnidadOrganizacional`),
  KEY `idUnidadPadre` (`idUnidadPadre`),
  KEY `idPais` (`idPais`),
  KEY `UnidadOrganizacional_Equipo` (`idEquipo`),
  KEY `IDX_62D8C141ED97AD04` (`idCiudad`),
  CONSTRAINT `UnidadOrganizacional_ibfk_2` FOREIGN KEY (`idUnidadPadre`) REFERENCES `UnidadOrganizacional` (`idUnidadOrganizacional`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;




/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
