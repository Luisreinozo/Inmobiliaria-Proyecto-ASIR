-- =========================================================
-- Configuración recomendada
-- =========================================================
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
SET sql_mode = 'STRICT_ALL_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

-- =========================================================
-- 1) CCAA
-- =========================================================
DROP TABLE IF EXISTS ccaa;
CREATE TABLE ccaa (
  id_ccaa SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  comunidad VARCHAR(50) NOT NULL,
  PRIMARY KEY (id_ccaa)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- =========================================================
-- 2) Provincias
-- =========================================================
DROP TABLE IF EXISTS provincias;
CREATE TABLE provincias (
  id_provincia SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  provincia VARCHAR(50) NOT NULL,
  ccaa_id_ccaa SMALLINT UNSIGNED NOT NULL,
  PRIMARY KEY (id_provincia),
  KEY ix_provincias_ccaa (ccaa_id_ccaa),
  CONSTRAINT provincias_ccaa_FK
    FOREIGN KEY (ccaa_id_ccaa) REFERENCES ccaa (id_ccaa)
    ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- =========================================================
-- 3) Localidades
-- =========================================================
DROP TABLE IF EXISTS localidades;
CREATE TABLE localidades (
  id_localidades SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  localidad VARCHAR(50) NOT NULL,
  provincias_id_provincia SMALLINT UNSIGNED NOT NULL,
  PRIMARY KEY (id_localidades),
  KEY ix_localidades_provincia (provincias_id_provincia),
  CONSTRAINT localidades_provincias_FK
    FOREIGN KEY (provincias_id_provincia) REFERENCES provincias (id_provincia)
    ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- =========================================================
-- 4) Operaciones (venta, alquiler, etc.)
-- =========================================================
DROP TABLE IF EXISTS operaciones;
CREATE TABLE operaciones (
  id_operaciones SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  operacion VARCHAR(20) NOT NULL,
  PRIMARY KEY (id_operaciones)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- =========================================================
-- 5) Propietarios
-- =========================================================
DROP TABLE IF EXISTS propietarios;
CREATE TABLE propietarios (
  dni_propietario VARCHAR(9) NOT NULL,
  nombre    VARCHAR(20) NOT NULL,
  apellido1 VARCHAR(50) NOT NULL,
  apellido2 VARCHAR(50) NULL,
  telefono  VARCHAR(15) NOT NULL,
  email     VARCHAR(190) NULL,
  direccion VARCHAR(100) NOT NULL,
  localidades_id_localidades SMALLINT UNSIGNED NOT NULL,
  PRIMARY KEY (dni_propietario),
  UNIQUE KEY propietarios_telefono_UN (telefono),
  UNIQUE KEY propietarios_email_UN (email),
  KEY ix_propietarios_localidad (localidades_id_localidades),
  CONSTRAINT propietarios_localidades_FK
    FOREIGN KEY (localidades_id_localidades) REFERENCES localidades (id_localidades)
    ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- =========================================================
-- 6) Compradores
-- =========================================================
DROP TABLE IF EXISTS compradores;
CREATE TABLE compradores (
  dni_comprador VARCHAR(9) NOT NULL,
  nombre    VARCHAR(20) NOT NULL,
  apellido1 VARCHAR(50) NOT NULL,
  apellido2 VARCHAR(50) NULL,
  telefono  VARCHAR(15) NOT NULL,
  email     VARCHAR(190) NULL,
  direccion VARCHAR(100) NOT NULL,
  localidades_id_localidades SMALLINT UNSIGNED NOT NULL,
  PRIMARY KEY (dni_comprador),
  UNIQUE KEY compradores_telefono_UN (telefono),
  UNIQUE KEY compradores_email_UN (email),
  KEY ix_compradores_localidad (localidades_id_localidades),
  CONSTRAINT compradores_localidades_FK
    FOREIGN KEY (localidades_id_localidades) REFERENCES localidades (id_localidades)
    ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- =========================================================
-- 7) Grupos (AD)
-- =========================================================
DROP TABLE IF EXISTS grupos;
CREATE TABLE grupos (
  id_grupo SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  grupo VARCHAR(50) NOT NULL,
  ruta  VARCHAR(512) NOT NULL, 
  PRIMARY KEY (id_grupo),
  UNIQUE KEY uq_grupos_grupo (grupo),
  UNIQUE KEY uq_grupos_ruta  (ruta)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- =========================================================
-- 8) Unidades organizativas (OU)
-- =========================================================
DROP TABLE IF EXISTS unidades_organizativas;
CREATE TABLE unidades_organizativas (
  id_uo SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  nombre VARCHAR(50) NOT NULL,
  ruta    VARCHAR(512) NOT NULL, 
  PRIMARY KEY (id_uo),
  UNIQUE KEY uq_uo_ruta (ruta)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- =========================================================
-- 9) Empleados
-- =========================================================
DROP TABLE IF EXISTS empleados;
CREATE TABLE empleados (
  id_empleado SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  ad_user     VARCHAR(64)  NOT NULL, 
  nombre      VARCHAR(20)  NOT NULL,
  apellido1   VARCHAR(20)  NOT NULL,
  apellido2   VARCHAR(20)  NULL,
  dni_empleado VARCHAR(9)  NOT NULL,
  email       VARCHAR(190) NOT NULL,
  telefono    VARCHAR(15)  NOT NULL,
  unidades_organizativas_id_uo SMALLINT UNSIGNED NOT NULL,
  grupos_id_grupo SMALLINT UNSIGNED NOT NULL,
  localidades_id_localidades SMALLINT UNSIGNED NOT NULL,
  PRIMARY KEY (id_empleado),
  UNIQUE KEY empleados_ad_user_UN (ad_user),
  UNIQUE KEY empleados_email_UN (email),
  UNIQUE KEY empleados_dni_UN (dni_empleado),
  KEY ix_empleados_uo (unidades_organizativas_id_uo),
  KEY ix_empleados_grupo (grupos_id_grupo),
  KEY ix_empleados_localidad (localidades_id_localidades),
  CONSTRAINT empleados_unidades_organizativas_FK
    FOREIGN KEY (unidades_organizativas_id_uo) REFERENCES unidades_organizativas (id_uo)
    ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT empleados_grupos_FK
    FOREIGN KEY (grupos_id_grupo) REFERENCES grupos (id_grupo)
    ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT empleados_localidades_FK
    FOREIGN KEY (localidades_id_localidades) REFERENCES localidades (id_localidades)
    ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- =========================================================
-- 10) ad_recursos (catálogo de recursos con permisos)
-- =========================================================
DROP TABLE IF EXISTS ad_recursos;
CREATE TABLE ad_recursos (
  codigo  VARCHAR(20) NOT NULL,
  recurso VARCHAR(50) NOT NULL, 
  PRIMARY KEY (codigo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- =========================================================
-- 11) ad_permisos (matriz grupo × recurso con CRUD)
-- =========================================================
DROP TABLE IF EXISTS ad_permisos;
CREATE TABLE ad_permisos (
  ad_recursos_codigo VARCHAR(20) NOT NULL,
  grupos_id_grupo SMALLINT UNSIGNED NOT NULL,
  crear      TINYINT(1) NOT NULL DEFAULT 0,
  leer       TINYINT(1) NOT NULL DEFAULT 1,
  actualizar TINYINT(1) NOT NULL DEFAULT 0,
  borrar     TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (ad_recursos_codigo, grupos_id_grupo),
  KEY ix_ad_permisos_grupo (grupos_id_grupo),
  CONSTRAINT ad_permisos_ad_recursos_FK
    FOREIGN KEY (ad_recursos_codigo) REFERENCES ad_recursos (codigo)
    ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT ad_permisos_grupos_FK
    FOREIGN KEY (grupos_id_grupo) REFERENCES grupos (id_grupo)
    ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- =========================================================
-- 12) Inmuebles
-- =========================================================
DROP TABLE IF EXISTS inmuebles;
CREATE TABLE inmuebles (
  id_inmueble INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nombre    VARCHAR(50) NOT NULL,
  zona      VARCHAR(50) NOT NULL,
  tipo      VARCHAR(50) NOT NULL,
  metros    DECIMAL(8,2) NOT NULL, 
  pvp       DECIMAL(10,2) NOT NULL,
  dormitorios TINYINT UNSIGNED NULL,
  garaje      TINYINT(1) NULL,     
  ascensor    TINYINT(1) NULL,     
  banos       TINYINT UNSIGNED NOT NULL,
  trastero    TINYINT(1) NULL,     
  -- NUEVA COLUMNA para control de publicación en la web
  publicado   TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1=Visible en la web, 0=Borrador/Oculto',
  propietarios_dni_propietario VARCHAR(9) NOT NULL,
  localidades_id_localidades SMALLINT UNSIGNED NOT NULL,
  operaciones_id_operaciones SMALLINT UNSIGNED NOT NULL,
  PRIMARY KEY (id_inmueble),
  KEY ix_inmuebles_localidad   (localidades_id_localidades),
  KEY ix_inmuebles_operacion   (operaciones_id_operaciones),
  KEY ix_inmuebles_propietario (propietarios_dni_propietario),
  CONSTRAINT inmuebles_localidades_FK
    FOREIGN KEY (localidades_id_localidades) REFERENCES localidades (id_localidades)
    ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT inmuebles_operaciones_FK
    FOREIGN KEY (operaciones_id_operaciones) REFERENCES operaciones (id_operaciones)
    ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT inmuebles_propietarios_FK
    FOREIGN KEY (propietarios_dni_propietario) REFERENCES propietarios (dni_propietario)
    ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- =========================================================
-- 13) Fotos
-- =========================================================
DROP TABLE IF EXISTS fotos;
CREATE TABLE fotos (
  id_fotos SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  inmuebles_id_inmueble INT UNSIGNED NOT NULL,
  ruta VARCHAR(512) NOT NULL, 
  PRIMARY KEY (id_fotos),
  KEY ix_fotos_inmueble (inmuebles_id_inmueble),
  CONSTRAINT fotos_inmuebles_FK
    FOREIGN KEY (inmuebles_id_inmueble) REFERENCES inmuebles (id_inmueble)
    ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- =========================================================
-- 14) Ventas (cabecera)
-- =========================================================
DROP TABLE IF EXISTS ventas;
CREATE TABLE ventas (
  id_venta SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  fecha DATE NOT NULL,
  oferta_final DECIMAL(10,2) NOT NULL,
  empleados_id_empleado SMALLINT UNSIGNED NOT NULL,
  compradores_dni_comprador VARCHAR(9) NOT NULL,
  PRIMARY KEY (id_venta),
  KEY ix_ventas_empleado (empleados_id_empleado),
  KEY ix_ventas_comprador (compradores_dni_comprador),
  CONSTRAINT ventas_empleados_FK
    FOREIGN KEY (empleados_id_empleado) REFERENCES empleados (id_empleado)
    ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT ventas_compradores_FK
    FOREIGN KEY (compradores_dni_comprador) REFERENCES compradores (dni_comprador)
    ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- =========================================================
-- 15) Detalle de venta (líneas)
-- =========================================================
DROP TABLE IF EXISTS detalle_venta;
CREATE TABLE detalle_venta (
  ventas_id_venta SMALLINT UNSIGNED NOT NULL,
  inmuebles_id_inmueble INT UNSIGNED NOT NULL,
  pvp_individual DECIMAL(12,2) NOT NULL,
  PRIMARY KEY (ventas_id_venta, inmuebles_id_inmueble),
  KEY ix_detalle_inmueble (inmuebles_id_inmueble),
  CONSTRAINT detalle_venta_ventas_FK
    FOREIGN KEY (ventas_id_venta) REFERENCES ventas (id_venta)
    ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT detalle_venta_inmuebles_FK
    FOREIGN KEY (inmuebles_id_inmueble) REFERENCES inmuebles (id_inmueble)
    ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- =========================================================
SET FOREIGN_KEY_CHECKS = 1;
