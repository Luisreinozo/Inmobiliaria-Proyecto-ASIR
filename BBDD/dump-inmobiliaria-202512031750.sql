/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19-11.7.2-MariaDB, for Win64 (AMD64)
--
-- Host: 10.0.2.22    Database: inmobiliaria
-- ------------------------------------------------------
-- Server version	10.11.13-MariaDB-0ubuntu0.24.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*M!100616 SET @OLD_NOTE_VERBOSITY=@@NOTE_VERBOSITY, NOTE_VERBOSITY=0 */;

--
-- Table structure for table `ccaa`
--

DROP TABLE IF EXISTS `ccaa`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ccaa` (
  `id_ccaa` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `comunidad` varchar(50) NOT NULL,
  PRIMARY KEY (`id_ccaa`),
  UNIQUE KEY `ccaa_comunidad_UN` (`comunidad`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `clientes`
--

DROP TABLE IF EXISTS `clientes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `clientes` (
  `id_cliente` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `apellido1` varchar(50) NOT NULL,
  `apellido2` varchar(50) DEFAULT NULL,
  `nif` varchar(15) NOT NULL,
  `telefono` varchar(20) NOT NULL,
  `email` varchar(190) NOT NULL,
  `direccion` varchar(100) NOT NULL,
  `localidades_id_localidades` smallint(5) unsigned NOT NULL,
  `fecha_alta` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_modificacion` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `usuario_creacion` smallint(5) unsigned NOT NULL,
  `usuario_modificacion` smallint(5) unsigned DEFAULT NULL,
  PRIMARY KEY (`id_cliente`),
  UNIQUE KEY `clientes_nif_UN` (`nif`),
  UNIQUE KEY `clientes_tel_UN` (`telefono`),
  UNIQUE KEY `clientes_email_UN` (`email`),
  KEY `clientes_localidades_FK` (`localidades_id_localidades`),
  KEY `clientes_empleados_creacion_FK` (`usuario_creacion`),
  KEY `clientes_empleados_modificacion_FK` (`usuario_modificacion`),
  CONSTRAINT `clientes_empleados_creacion_FK` FOREIGN KEY (`usuario_creacion`) REFERENCES `empleados` (`id_empleado`),
  CONSTRAINT `clientes_empleados_modificacion_FK` FOREIGN KEY (`usuario_modificacion`) REFERENCES `empleados` (`id_empleado`) ON DELETE SET NULL,
  CONSTRAINT `clientes_localidades_FK` FOREIGN KEY (`localidades_id_localidades`) REFERENCES `localidades` (`id_localidades`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `detalle_venta`
--

DROP TABLE IF EXISTS `detalle_venta`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `detalle_venta` (
  `ventas_id_venta` smallint(5) unsigned NOT NULL,
  `inmuebles_id_inmueble` int(10) unsigned NOT NULL,
  `pvp_individual` decimal(12,2) NOT NULL,
  PRIMARY KEY (`ventas_id_venta`,`inmuebles_id_inmueble`),
  KEY `detalle_venta_inmuebles_FK` (`inmuebles_id_inmueble`),
  CONSTRAINT `detalle_venta_inmuebles_FK` FOREIGN KEY (`inmuebles_id_inmueble`) REFERENCES `inmuebles` (`id_inmueble`),
  CONSTRAINT `detalle_venta_ventas_FK` FOREIGN KEY (`ventas_id_venta`) REFERENCES `ventas` (`id_venta`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `empleados`
--

DROP TABLE IF EXISTS `empleados`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `empleados` (
  `id_empleado` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `apellido1` varchar(50) NOT NULL,
  `apellido2` varchar(50) DEFAULT NULL,
  `nif` varchar(15) NOT NULL,
  `telefono` varchar(20) NOT NULL,
  `email` varchar(190) NOT NULL,
  `cargo` varchar(50) DEFAULT NULL,
  `nombre_usuario_ad` varchar(255) NOT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_modificacion` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_empleado`),
  UNIQUE KEY `empleados_nif_UN` (`nif`),
  UNIQUE KEY `empleados_tel_UN` (`telefono`),
  UNIQUE KEY `empleados_email_UN` (`email`),
  UNIQUE KEY `empleados_username_UN` (`nombre_usuario_ad`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fotos`
--

DROP TABLE IF EXISTS `fotos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `fotos` (
  `id_fotos` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `inmuebles_id_inmueble` int(10) unsigned NOT NULL,
  `ruta` varchar(512) NOT NULL,
  `orden` int(10) unsigned NOT NULL DEFAULT 1,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_modificacion` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `usuario_creacion` smallint(5) unsigned NOT NULL,
  `usuario_modificacion` smallint(5) unsigned DEFAULT NULL,
  PRIMARY KEY (`id_fotos`),
  UNIQUE KEY `fotos_inmueble_ruta_UN` (`inmuebles_id_inmueble`,`ruta`),
  KEY `fotos_empleados_creacion_FK` (`usuario_creacion`),
  KEY `fotos_empleados_modificacion_FK` (`usuario_modificacion`),
  CONSTRAINT `fotos_empleados_creacion_FK` FOREIGN KEY (`usuario_creacion`) REFERENCES `empleados` (`id_empleado`),
  CONSTRAINT `fotos_empleados_modificacion_FK` FOREIGN KEY (`usuario_modificacion`) REFERENCES `empleados` (`id_empleado`) ON DELETE SET NULL,
  CONSTRAINT `fotos_inmuebles_FK` FOREIGN KEY (`inmuebles_id_inmueble`) REFERENCES `inmuebles` (`id_inmueble`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `inmuebles`
--

DROP TABLE IF EXISTS `inmuebles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `inmuebles` (
  `id_inmueble` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `titulo` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `zona` varchar(50) NOT NULL,
  `id_tipo` smallint(5) unsigned NOT NULL,
  `metros` decimal(8,2) NOT NULL,
  `pvp` decimal(12,2) NOT NULL,
  `dormitorios` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `banos` tinyint(3) unsigned NOT NULL,
  `garaje` tinyint(1) DEFAULT NULL,
  `ascensor` tinyint(1) DEFAULT NULL,
  `trastero` tinyint(1) DEFAULT NULL,
  `publicado` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1=Visible en la web, 0=Oculto/Borrador',
  `clientes_id_propietario` int(10) unsigned NOT NULL,
  `localidades_id_localidades` smallint(5) unsigned NOT NULL,
  `operaciones_id_operaciones` smallint(5) unsigned NOT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_modificacion` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `usuario_creacion` smallint(5) unsigned NOT NULL,
  `usuario_modificacion` smallint(5) unsigned DEFAULT NULL,
  PRIMARY KEY (`id_inmueble`),
  KEY `inmuebles_tipos_inmueble_FK` (`id_tipo`),
  KEY `inmuebles_localidades_FK` (`localidades_id_localidades`),
  KEY `inmuebles_operaciones_FK` (`operaciones_id_operaciones`),
  KEY `inmuebles_clientes_propietario_FK` (`clientes_id_propietario`),
  KEY `inmuebles_empleados_creacion_FK` (`usuario_creacion`),
  KEY `inmuebles_empleados_modificacion_FK` (`usuario_modificacion`),
  CONSTRAINT `inmuebles_clientes_propietario_FK` FOREIGN KEY (`clientes_id_propietario`) REFERENCES `clientes` (`id_cliente`),
  CONSTRAINT `inmuebles_empleados_creacion_FK` FOREIGN KEY (`usuario_creacion`) REFERENCES `empleados` (`id_empleado`),
  CONSTRAINT `inmuebles_empleados_modificacion_FK` FOREIGN KEY (`usuario_modificacion`) REFERENCES `empleados` (`id_empleado`) ON DELETE SET NULL,
  CONSTRAINT `inmuebles_localidades_FK` FOREIGN KEY (`localidades_id_localidades`) REFERENCES `localidades` (`id_localidades`),
  CONSTRAINT `inmuebles_operaciones_FK` FOREIGN KEY (`operaciones_id_operaciones`) REFERENCES `operaciones` (`id_operaciones`),
  CONSTRAINT `inmuebles_tipos_inmueble_FK` FOREIGN KEY (`id_tipo`) REFERENCES `tipos_inmueble` (`id_tipo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `localidades`
--

DROP TABLE IF EXISTS `localidades`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `localidades` (
  `id_localidades` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `localidad` varchar(50) NOT NULL,
  `provincias_id_provincia` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`id_localidades`),
  UNIQUE KEY `localidades_UN` (`localidad`,`provincias_id_provincia`),
  KEY `localidades_provincias_FK` (`provincias_id_provincia`),
  CONSTRAINT `localidades_provincias_FK` FOREIGN KEY (`provincias_id_provincia`) REFERENCES `provincias` (`id_provincia`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `operaciones`
--

DROP TABLE IF EXISTS `operaciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `operaciones` (
  `id_operaciones` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `operacion` varchar(20) NOT NULL,
  PRIMARY KEY (`id_operaciones`),
  UNIQUE KEY `operaciones_operacion_UN` (`operacion`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `provincias`
--

DROP TABLE IF EXISTS `provincias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `provincias` (
  `id_provincia` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `provincia` varchar(50) NOT NULL,
  `ccaa_id_ccaa` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`id_provincia`),
  UNIQUE KEY `provincias_provincia_UN` (`provincia`),
  KEY `provincias_ccaa_FK` (`ccaa_id_ccaa`),
  CONSTRAINT `provincias_ccaa_FK` FOREIGN KEY (`ccaa_id_ccaa`) REFERENCES `ccaa` (`id_ccaa`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tareas_nextcloud`
--

DROP TABLE IF EXISTS `tareas_nextcloud`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tareas_nextcloud` (
  `id_tarea` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_inmueble` int(10) unsigned NOT NULL,
  `accion` enum('CREAR_CARPETA','BORRAR_CARPETA') NOT NULL,
  `estado` enum('PENDIENTE','OK','ERROR') NOT NULL DEFAULT 'PENDIENTE',
  `detalle_error` varchar(255) DEFAULT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_ejecucion` datetime DEFAULT NULL,
  PRIMARY KEY (`id_tarea`),
  KEY `tareas_nextcloud_inmuebles_FK` (`id_inmueble`),
  CONSTRAINT `tareas_nextcloud_inmuebles_FK` FOREIGN KEY (`id_inmueble`) REFERENCES `inmuebles` (`id_inmueble`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tipos_inmueble`
--

DROP TABLE IF EXISTS `tipos_inmueble`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tipos_inmueble` (
  `id_tipo` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `tipo` varchar(50) NOT NULL,
  PRIMARY KEY (`id_tipo`),
  UNIQUE KEY `tipos_inmueble_tipo_UN` (`tipo`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ventas`
--

DROP TABLE IF EXISTS `ventas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ventas` (
  `id_venta` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `fecha_venta` date NOT NULL,
  `clientes_id_comprador` int(10) unsigned NOT NULL,
  `empleados_id_empleado` smallint(5) unsigned NOT NULL,
  `precio_total` decimal(12,2) NOT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_modificacion` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `usuario_creacion` smallint(5) unsigned NOT NULL,
  `usuario_modificacion` smallint(5) unsigned DEFAULT NULL,
  PRIMARY KEY (`id_venta`),
  KEY `ventas_clientes_comprador_FK` (`clientes_id_comprador`),
  KEY `ventas_empleados_FK` (`empleados_id_empleado`),
  KEY `ventas_empleados_creacion_FK` (`usuario_creacion`),
  KEY `ventas_empleados_modificacion_FK` (`usuario_modificacion`),
  CONSTRAINT `ventas_clientes_comprador_FK` FOREIGN KEY (`clientes_id_comprador`) REFERENCES `clientes` (`id_cliente`),
  CONSTRAINT `ventas_empleados_FK` FOREIGN KEY (`empleados_id_empleado`) REFERENCES `empleados` (`id_empleado`),
  CONSTRAINT `ventas_empleados_creacion_FK` FOREIGN KEY (`usuario_creacion`) REFERENCES `empleados` (`id_empleado`),
  CONSTRAINT `ventas_empleados_modificacion_FK` FOREIGN KEY (`usuario_modificacion`) REFERENCES `empleados` (`id_empleado`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping routines for database 'inmobiliaria'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*M!100616 SET NOTE_VERBOSITY=@OLD_NOTE_VERBOSITY */;

-- Dump completed on 2025-12-03 17:50:39
